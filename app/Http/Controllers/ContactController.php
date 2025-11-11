<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Show the contact form
     */
    public function index()
    {
        return view('contact');
    }

    /**
     * Handle contact form submission
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'privacy' => 'required|accepted'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Store contact message in database (optional)
            // You can create a ContactMessage model and save it here
            
            // Send email notification
            $this->sendContactEmail($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your message has been sent successfully. We will get back to you soon.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an error sending your message. Please try again or contact us directly.'
            ], 500);
        }
    }

    /**
     * Send contact email
     */
    private function sendContactEmail($data)
    {
        $adminEmail = config('mail.admin_email', 'moshoodkayodeabdul@gmail.com');
        
        // Simple email sending - you can enhance this with a proper mail template
        $subject = 'New Contact Form Submission: ' . $data['subject'];
        $message = "
            New contact form submission from EasyRent website:
            
            Name: {$data['name']}
            Email: {$data['email']}
            Phone: " . ($data['phone'] ?? 'Not provided') . "
            Subject: {$data['subject']}
            
            Message:
            {$data['message']}
            
            Submitted at: " . now()->format('Y-m-d H:i:s');

        // Send email using Laravel's mail functionality
        try {
            Mail::raw($message, function ($mail) use ($data, $adminEmail, $subject) {
                $mail->to($adminEmail)
                     ->subject($subject)
                     ->replyTo($data['email'], $data['name']);
            });
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            \Log::error('Contact form email failed: ' . $e->getMessage());
        }
    }
}
