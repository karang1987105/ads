<x-form name="add" method="POST">
    @isset($message)
        <x-slot
            name="action">{{ route('tickets.messages.update', ['message' => $message->id, 'thread' => $thread->id], false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('tickets.messages.store', ['thread' => $thread->id],false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column type="full">
        <x-input.textarea name="message" value="{{ isset($message) ? $message->message : '' }}" required rows="10"/>
    </x-form-column>
</x-form>
