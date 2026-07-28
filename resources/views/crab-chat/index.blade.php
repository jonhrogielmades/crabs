@extends('layouts.app')
@section('content')
<section class="page-head chat-head">
    <div>
        <p class="eyebrow">Crab-only assistant</p>
        <h1>Crab Chatbot</h1>
        <p class="chat-head-copy">Ask crab-only identification, habitat, visual trait, taxonomy, and handling questions.</p>
    </div>
    <span class="badge chat-status-badge"><i data-lucide="shield-check"></i>Crab-only</span>
</section>

<section class="crab-chat" data-chat-endpoint="{{ route('crab-chat.message') }}">
    <div class="chat-log" id="chatLog" aria-live="polite">
        <article class="chat-message bot-message">
            <span class="chat-avatar"><i data-lucide="bot"></i></span>
            <div class="chat-bubble">
                <strong>CrabAI</strong>
                <p>What crab information do you need? I can help with identification clues, habitats, traits, taxonomy, and safe handling cautions.</p>
            </div>
        </article>
    </div>

    <div class="chat-suggestions" id="chatSuggestions">
        @foreach($suggestedSpecies as $item)
            <button type="button" data-chat-prompt="Tell me about {{ $item->common_name }}">{{ $item->common_name }}</button>
        @endforeach
        <button type="button" data-chat-prompt="How do I identify mud crabs?">Identify mud crabs</button>
        <button type="button" data-chat-prompt="What crab traits should I photograph?">Photo traits</button>
    </div>

    <form class="chat-form" id="crabChatForm">
        <label class="chat-input-wrap" for="crabChatInput">
            <i data-lucide="message-circle"></i>
            <textarea id="crabChatInput" name="message" rows="1" maxlength="1000" placeholder="Ask a crab question" required></textarea>
        </label>
        <button class="chat-send" type="submit" aria-label="Send crab question">
            <span class="button-spinner" aria-hidden="true"></span>
            <i data-lucide="send-horizontal"></i>
        </button>
    </form>
</section>
@endsection
