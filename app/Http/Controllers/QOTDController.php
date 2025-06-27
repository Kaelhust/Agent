<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class QuoteController extends Controller
{
    /**
     * Display the Quote of the Day.
     *
     * @return View
     */
    public function showQuote(): View
    {
        // In a real application, you would call your Python script here
        // For example:
        // $command = 'python /path/to/your/quote_of_the_day_ai.py';
        // $output = shell_exec($command);
        // Then parse the $output to get the quote and author.

        // For this example, we'll simulate fetching a quote.
        $quote = [
            'text' => 'The only way to do great work is to love what you do.',
            'author' => 'Steve Jobs'
        ];

        // You could also try to get a quote based on a topic
        // $quote = $this->getQuoteFromPython('motivation'); // Example call

        return view('ui', compact('quote'));
    }

    /**
     * (Optional) Get a new quote, possibly via AJAX.
     * This method would be used if you want to update the quote dynamically
     * without a full page reload.
     *
     * @param string|null $topic
     * @return JsonResponse
     */
    public function getNewQuote(Request $request): JsonResponse
    {
        $topic = $request->query('topic');

        // Simulate fetching a new quote.
        // In a real scenario, you'd execute your Python script with the topic.
        // Example: $command = 'python /path/to/your/quote_of_the_day_ai.py --topic ' . escapeshellarg($topic);
        // $output = shell_exec($command);
        // Parse $output for quote and author.

        if ($topic === 'innovation') {
            $quote = [
                'text' => 'Innovation distinguishes between a leader and a follower.',
                'author' => 'Steve Jobs'
            ];
        } elseif ($topic === 'wisdom') {
            $quote = [
                'text' => 'The only true wisdom is in knowing you know nothing.',
                'author' => 'Socrates'
            ];
        } else {
            $quotes = [
                ['text' => 'Believe you can and you\'re halfway there.', 'author' => 'Theodore Roosevelt'],
                ['text' => 'The future belongs to those who believe in the beauty of their dreams.', 'author' => 'Eleanor Roosevelt'],
                ['text' => 'It is during our darkest moments that we must focus to see the light.', 'author' => 'Aristotle']
            ];
            $quote = $quotes[array_rand($quotes)];
        }

        return response()->json($quote);
    }

    /**
     * Helper function to simulate calling the Python script.
     * In a real application, this would execute the Python script
     * and parse its output.
     *
     * @param string|null $topic
     * @return array
     */
    private function getQuoteFromPython(?string $topic = null): array
    {
        // This is where you would execute the Python script.
        // For example:
        // $pythonScriptPath = base_path('path/to/your/quote_of_the_day_ai.py');
        // $command = 'python ' . escapeshellarg($pythonScriptPath);
        // if ($topic) {
        //     $command .= ' --topic ' . escapeshellarg($topic); // Assuming your Python script accepts --topic argument
        // }
        // $output = shell_exec($command);

        // // Implement robust parsing of the Python script's output here
        // // The Python script would ideally output JSON for easy parsing.
        // $parsedOutput = json_decode($output, true);
        // if (json_last_error() === JSON_ERROR_NONE && isset($parsedOutput['text'])) {
        //     return [
        //         'text' => $parsedOutput['text'],
        //         'author' => $parsedOutput['author'] ?? 'Unknown'
        //     ];
        // }

        // Fallback or simulated data
        $simulatedQuotes = [
            ['text' => 'The journey of a thousand miles begins with a single step.', 'author' => 'Lao Tzu'],
            ['text' => 'Strive not to be a success, but rather to be of value.', 'author' => 'Albert Einstein'],
            ['text' => 'The mind is everything. What you think you become.', 'author' => 'Buddha'],
        ];

        if ($topic) {
            // Simple simulation for topic-based quotes
            if ($topic === 'motivation') {
                return ['text' => 'Motivation is what gets you started. Habit is what keeps you going.', 'author' => 'Jim Ryun'];
            }
        }

        return $simulatedQuotes[array_rand($simulatedQuotes)];
    }
}
