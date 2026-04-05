<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1c1917;">
    <p><strong>{{ __('Support topic label') }}:</strong> {{ $payload['topic_label'] }}</p>
    <p><strong>{{ __('Name') }}:</strong> {{ $payload['name'] }}</p>
    <p><strong>{{ __('Email') }}:</strong> {{ $payload['email'] }}</p>
    <p><strong>{{ __('Phone') }}:</strong> {{ $payload['phone'] ?? '—' }}</p>
    <p><strong>{{ __('Subject') }}:</strong> {{ $payload['subject'] }}</p>
    <hr style="border: none; border-top: 1px solid #e7e5e4; margin: 1rem 0;">
    <p style="white-space: pre-wrap;">{{ $payload['message'] }}</p>
</body>
</html>
