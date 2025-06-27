# fastapi_qotd_app/main.py

import os
import json
from typing import Optional, Type
from pydantic import BaseModel, Field
from fastapi import FastAPI, Query, HTTPException
from fastapi.middleware.cors import CORSMiddleware # For CORS if Laravel is on a different origin

# --- FIX: Updated import for Ollama LLM ---
from langchain_ollama import OllamaLLM # Use the new, dedicated Ollama LLM class
# Old: from langchain_community.llms import Ollama

from langchain_community.tools import DuckDuckGoSearchRun
from langchain_core.prompts import ChatPromptTemplate
from langchain_core.runnables import RunnablePassthrough
from langchain.agents import AgentExecutor, create_tool_calling_agent
from langchain.tools import tool
from pypdf import PdfReader # Included as requested, though not directly used

# --- FastAPI App Initialization ---
app = FastAPI(
    title="Quote of the Day AI API",
    description="An API to generate inspiring quotes based on context and grade level using Gemma:2b.",
    version="1.0.0"
)

# Add CORS middleware to allow requests from your Laravel frontend
# Adjust origins as needed for your Laravel application's URL
origins = [
    "http://localhost",
    "http://localhost:8000", # Default Laravel development server
    "http://127.0.0.1:8000", # Another common Laravel development server address
    "http://your-laragon-domain.test", # Your Laragon virtual host domain
    # Add other origins if your Laravel app is hosted elsewhere
]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- 1. Pydantic Models for Data Validation ---
class Quote(BaseModel):
    """Represents a quote with its text and optional author."""
    text: str = Field(description="The text of the quote.")
    author: Optional[str] = Field(None, description="The author of the quote, if known.")

# --- 2. Custom Tool for DuckDuckGo Search ---

# Initialize the DuckDuckGo search tool
duckduckgo_search = DuckDuckGoSearchRun()

@tool
def search_for_quotes(query: str) -> str:
    """
    Searches the web for quotes based on a given query.
    Useful for finding quotes on specific topics or by certain authors.
    """
    # print(f"\n--- Searching for quotes with query: '{query}' ---", file=sys.stderr) # For debugging
    return duckduckgo_search.run(query)

# --- 3. LLM Setup (Ollama with Gemma:2b) ---

llm = None # Initialize as None, set up in startup event
@app.on_event("startup")
async def startup_event():
    """Initialize Ollama LLM on application startup."""
    global llm
    try:
        # --- FIX: Use the new OllamaLLM class ---
        llm = OllamaLLM(model="gemma:2b")
        print("Ollama (gemma:2b) initialized successfully.")
    except Exception as e:
        print(f"Error initializing Ollama: {e}")
        print("Please ensure Ollama server is running and 'gemma:2b' model is available.")
        print("You can download Ollama from https://ollama.com/ and pull the model with 'ollama pull gemma:2b'")
        # It's better to let the endpoint handle the error if LLM isn't ready

# --- 4. Langchain Agent Setup ---

# Define the tools available to the agent
tools = [search_for_quotes]

# Define the prompt for the agent
prompt_template = """You are a helpful AI assistant specialized in finding and generating inspiring quotes.
When asked for a quote, you can either search for one or generate a new one.
If you search, try to extract the quote and author clearly.
Always provide a quote and its author if possible.

User Request: {input}

{context_instruction}
{grade_level_instruction}
"""

# --- 5. Quote Generation Logic (now an API endpoint) ---

@app.get("/quote", response_model=Quote)
async def get_quote_endpoint(
    topic: Optional[str] = Query(None, description="Optional topic/context for the quote."),
    grade_level: Optional[str] = Query(None, description="Optional grade level for the quote (e.g., Pre-K, University).")
):
    """
    Generates an inspiring quote based on the provided topic and grade level.
    """
    if llm is None:
        raise HTTPException(status_code=503, detail="AI service is not ready. Ollama model not loaded.")

    user_input_base = "Give me an inspiring quote of the day."
    if topic:
        user_input_base = f"Find an inspiring quote about {topic}."

    context_instruction = ""
    if topic:
        context_instruction = f"The quote should be relevant to the topic: '{topic}'."

    grade_level_instruction = ""
    if grade_level and grade_level != "General":
        grade_level_instruction = f"The quote should be appropriate and understandable for a '{grade_level}' audience."
    elif grade_level == "General":
        grade_level_instruction = "The quote should be suitable for a general audience."

    full_prompt = prompt_template.format(
        input=user_input_base,
        context_instruction=context_instruction,
        grade_level_instruction=grade_level_instruction
    )

    # Create the agent with the dynamic prompt
    agent_prompt = ChatPromptTemplate.from_messages([
        ("system", full_prompt),
        ("user", "{input}")
    ])
    agent = create_tool_calling_agent(llm, tools, agent_prompt)
    agent_executor = AgentExecutor(agent=agent, tools=tools, verbose=False) # Set verbose to False for production

    try:
        # Invoke the agent to get the quote
        result = agent_executor.invoke({"input": user_input_base}) # Pass a simple input, the full prompt is in system message
        quote_text = result.get("output", "No quote found or generated.")

        author = "Unknown"
        # Attempt to parse the quote and author from the output
        # For more robustness, instruct LLM to output JSON directly in the prompt
        if " - " in quote_text:
            parts = quote_text.rsplit(" - ", 1)
            quote_text = parts[0].strip().strip('"')
            author = parts[1].strip()
        elif "by " in quote_text.lower() and len(quote_text.split("by ")) > 1:
            parts = quote_text.lower().split("by ", 1)
            quote_text = quote_text[:quote_text.lower().find("by ")].strip().strip('"')
            author = parts[1].strip()
        else:
            # If no clear author, ask LLM to generate one or state unknown
            author_prompt = ChatPromptTemplate.from_template(
                "The following text is a quote: '{quote_text}'. Can you identify the author? If not, state 'Unknown'."
            )
            author_chain = {"quote_text": RunnablePassthrough()} | author_prompt | llm
            generated_author = author_chain.invoke(quote_text).strip()
            if generated_author and generated_author.lower() != "unknown" and len(generated_author) < 50:
                author = generated_author
            else:
                author = "Unknown"

        return Quote(text=quote_text, author=author)

    except Exception as e:
        print(f"An error occurred during quote generation: {e}") # Log to console
        raise HTTPException(status_code=500, detail=f"Internal AI error: {e}")

# --- Example of how pypdf could be used (not for quote generation here) ---
# This function is not exposed via API but kept for completeness as requested
def extract_text_from_pdf(pdf_path: str) -> str:
    """
    Extracts all text from a given PDF file.
    """
    try:
        reader = PdfReader(pdf_path)
        text = ""
        for page in reader.pages:
            text += page.extract_text() + "\n"
        return text
    except Exception as e:
        print(f"Error extracting text from PDF {pdf_path}: {e}")
        return ""

