<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function index()
    {
        $top5Faqs = FAQ::whereNotNull('answer')->where('display', true)->limit(5)->latest('updated_at')->get();
        return response()->json(['success' => true, 'data' => $top5Faqs], 200);
    }

    public function noAnswersFAQs()
    {
        $top5Faqs = FAQ::limit(5)->latest()->get();
        return response()->json(['success' => true, 'data' => $top5Faqs], 200);
    }

    public function question(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'email' => 'nullable|email',
        ]);

        $faq = FAQ::create([
            'question' => $request->input('question'),
            'email' => $request->input('email'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thanks for submitting your question!',
            'data' => $faq
        ], 201);
    }

    public function answer(Request $request, FAQ $faq)
    {
        $validatedData = $request->validate([
            'answer' => 'required|string',
            'display' => 'boolean',
        ]);

        $faq->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'The FAQ has been successfully answered!',
            'data' => $faq
        ], 201);
    }
}
