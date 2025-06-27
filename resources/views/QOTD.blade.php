<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote of the Day AI</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-600 to-blue-500 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-xl shadow-2xl p-8 md:p-12 max-w-lg w-full text-center transform transition-all duration-300 hover:scale-105">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-6 leading-tight">
            Quote of the Day AI
        </h1>

        <div class="mb-6 space-y-4">
            <!-- Context/Topic Input -->
            <div>
                <label for="context-input" class="block text-gray-700 text-sm font-bold mb-2 text-left">
                    Context/Topic (e.g., "motivation", "innovation"):
                </label>
                <input type="text" id="context-input" placeholder="Enter a topic or context"
                       class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition duration-200 ease-in-out">
            </div>

            <!-- Grade Level Selector -->
            <div>
                <label for="grade-level-select" class="block text-gray-700 text-sm font-bold mb-2 text-left">
                    Select Grade Level:
                </label>
                <select id="grade-level-select"
                        class="block appearance-none w-full bg-white border border-gray-300 text-gray-700 py-3 px-4 pr-8 rounded-lg shadow leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition duration-200 ease-in-out">
                    <option value="">Any Grade Level</option>
                    <option value="Pre-K">Pre-K</option>
                    <option value="Elementary School">Elementary School</option>
                    <option value="Middle School">Middle School</option>
                    <option value="High School">High School</option>
                    <option value="University">University</option>
                    <option value="General">General Audience</option>
                </select>
            </div>
        </div>

        <div id="quote-display" class="mb-8 min-h-[100px] flex flex-col justify-center items-center">
            <p class="text-2xl md:text-3xl italic text-gray-700 leading-relaxed mb-4">
                "{{ $quote['text'] }}"
            </p>
            <p class="text-lg md:text-xl font-semibold text-gray-600">
                - {{ $quote['author'] }}
            </p>
        </div>

        <button id="new-quote-btn"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-full shadow-lg transition-all duration-300 ease-in-out transform hover:-translate-y-1 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-300">
            Generate New Quote
        </button>

        <div id="loading-spinner" class="hidden mt-4 text-blue-600">
            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2 text-sm">Generating quote...</p>
        </div>
    </div>

    <script>
        document.getElementById('new-quote-btn').addEventListener('click', async () => {
            const contextInput = document.getElementById('context-input');
            const gradeLevelSelect = document.getElementById('grade-level-select');
            const quoteDisplay = document.getElementById('quote-display');
            const loadingSpinner = document.getElementById('loading-spinner');
            const newQuoteBtn = document.getElementById('new-quote-btn');

            const topic = contextInput.value.trim();
            const gradeLevel = gradeLevelSelect.value;

            quoteDisplay.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            newQuoteBtn.disabled = true;
            loadingSpinner.classList.remove('hidden');

            try {
                let url = '/get-new-quote';
                const params = new URLSearchParams();
                if (topic) {
                    params.append('topic', topic);
                }
                if (gradeLevel) {
                    params.append('grade_level', gradeLevel);
                }
                if (params.toString()) {
                    url += '?' + params.toString();
                }

                const response = await fetch(url);
                const data = await response.json();

                if (data.text) {
                    document.querySelector('#quote-display p:first-child').textContent = `"${data.text}"`;
                    document.querySelector('#quote-display p:last-child').textContent = `- ${data.author || 'Unknown'}`;
                } else {
                    document.querySelector('#quote-display p:first-child').textContent = `"Failed to generate quote. Please try again."`;
                    document.querySelector('#quote-display p:last-child').textContent = `- AI System`;
                }
            } catch (error) {
                console.error('Error fetching new quote:', error);
                document.querySelector('#quote-display p:first-child').textContent = `"An error occurred while fetching the quote."`;
                document.querySelector('#quote-display p:last-child').textContent = `- AI System`;
            } finally {
                loadingSpinner.classList.add('hidden');
                newQuoteBtn.disabled = false;
                quoteDisplay.classList.remove('opacity-0');
            }
        });
    </script>
</body>
</html>
