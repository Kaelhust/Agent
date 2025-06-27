<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http; // For making HTTP requests to FastAPI
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Validation\ValidationException; // For validation errors

class QuoteController extends Controller
{
    /**
     * Display the Quote of the Day UI.
     * This method is called when the user visits the root URL ('/').
     *
     * @return View
     */
    public function showQuote(): View
    {
        Log::info('Displaying Quote of the Day UI.');

        // Initial quote displayed when the page first loads.
        // It's a placeholder before the user generates a new one via AJAX.
        $quote = [
            'text' => 'Welcome! Generate your first quote.',
            'author' => 'AI System'
        ];

        return view('ui', compact('quote'));
    }

    /**
     * Get a new quote based on user input (topic and grade level) from FastAPI.
     * This method is called via AJAX from the frontend using a GET request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getNewQuote(Request $request): JsonResponse
    {
        Log::info('Processing GET request for new quote.', $request->all());

        try {
            // Validate incoming request data, similar to QuizmeController's processForm
            $validatedData = $request->validate([
                'topic' => 'nullable|string|max:255',
                'grade_level' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed for getNewQuote: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Validation failed: ' . $e->getMessage(),
                'details' => $e->errors()
            ], 422); // 422 Unprocessable Entity for validation errors
        }

        $topic = $validatedData['topic'] ?? null;
        $gradeLevel = $validatedData['grade_level'] ?? null;

        // --- IMPORTANT: Set the correct URL for your FastAPI application ---
        $fastApiUrl = env('FASTAPI_QOTD_URL', 'http://127.0.0.1:8001');
        $endpoint = $fastApiUrl . '/quote';

        $queryParams = [];
        if ($topic) {
            $queryParams['topic'] = $topic;
        }
        if ($gradeLevel) {
            $queryParams['grade_level'] = $gradeLevel;
        }

        try {
            Log::info('Making HTTP GET request to FastAPI for quote generation.', ['endpoint' => $endpoint, 'params' => $queryParams]);
            $response = Http::timeout(60)->get($endpoint, $queryParams); // Set a timeout for the API request

            if ($response->successful()) {
                $quoteData = $response->json();
                Log::info('Successfully received quote from FastAPI.', ['quote' => $quoteData]);
                return response()->json([
                    'text' => $quoteData['text'] ?? 'No quote text received.',
                    'author' => $quoteData['author'] ?? 'Unknown'
                ]);
            } else {
                // Log the error for debugging, including response body
                $errorMessage = 'FastAPI request failed: ' . $response->status() . ' - ' . ($response->json()['detail'] ?? $response->body());
                Log::error('FastAPI GET quote call failed: ' . $errorMessage);
                return response()->json([
                    'text' => 'AI service error: Could not retrieve quote.',
                    'author' => 'AI System'
                ], $response->status());
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('FastAPI connection error for GET quote: ' . $e->getMessage());
            return response()->json([
                'text' => 'AI service is unreachable. Please ensure the FastAPI server is running.',
                'author' => 'System Error'
            ], 503);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while calling FastAPI for GET quote: ' . $e->getMessage());
            return response()->json([
                'text' => 'An unexpected error occurred. Please try again later.',
                'author' => 'AI System'
            ], 500);
        }
    }

    /**
     * Handles POST requests for quote generation (if your UI were to use a form submission).
     * This method is a placeholder, mirroring the structure of processForm from QuizmeController.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitQuote(Request $request): JsonResponse
    {
        Log::info('Processing POST request for new quote.', $request->all());

        try {
            // Validate incoming request data, similar to QuizmeController's processForm
            $validatedData = $request->validate([
                'topic' => 'nullable|string|max:255',
                'grade_level' => 'nullable|string|max:255',
                // Add other fields if your POST form sends them
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed for submitQuote: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Validation failed: ' . $e->getMessage(),
                'details' => $e->errors()
            ], 422);
        }

        $topic = $validatedData['topic'] ?? null;
        $gradeLevel = $validatedData['grade_level'] ?? null;

        // --- IMPORTANT: Set the correct URL for your FastAPI application ---
        $fastApiUrl = env('FASTAPI_QOTD_URL', 'http://127.0.0.1:8001');
        $endpoint = $fastApiUrl . '/quote'; // Assuming FastAPI /quote endpoint supports POST

        $postData = [];
        if ($topic) {
            $postData['topic'] = $topic;
        }
        if ($gradeLevel) {
            $postData['grade_level'] = $gradeLevel;
        }

        try {
            Log::info('Making HTTP POST request to FastAPI for quote generation.', ['endpoint' => $endpoint, 'data' => $postData]);
            $response = Http::timeout(60)->post($endpoint, $postData); // Use post() for POST requests

            if ($response->successful()) {
                $quoteData = $response->json();
                Log::info('Successfully received quote from FastAPI via POST.', ['quote' => $quoteData]);
                return response()->json([
                    'text' => $quoteData['text'] ?? 'No quote text received.',
                    'author' => $quoteData['author'] ?? 'Unknown'
                ]);
            } else {
                $errorMessage = 'FastAPI POST request failed: ' . $response->status() . ' - ' . ($response->json()['detail'] ?? $response->body());
                Log::error('FastAPI POST quote call failed: ' . $errorMessage);
                return response()->json([
                    'text' => 'AI service error: Could not retrieve quote via POST.',
                    'author' => 'AI System'
                ], $response->status());
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('FastAPI connection error for POST quote: ' . $e->getMessage());
            return response()->json([
                'text' => 'AI service is unreachable. Please ensure the FastAPI server is running.',
                'author' => 'System Error'
            ], 503);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred while calling FastAPI for POST quote: ' . $e->getMessage());
            return response()->json([
                'text' => 'An unexpected error occurred. Please try again later.',
                'author' => 'AI System'
            ], 500);
        }
    }
}
