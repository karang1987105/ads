<x-form name="add" method="POST" enctype="multipart/form-data">
    <x-slot name="action">{{ route('admin.emails-templates.send', ['template' => $template, 'direct' => $direct], false) }}</x-slot>
    <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    <x-slot name="submitLabel">Send</x-slot>
{{--    <x-slot name="buttons">--}}
{{--        <button onclick="Ads.item.submitForm(this, {test:1})" type="button"--}}
{{--                class="mb-2 btn btn-secondary mr-2">Test--}}
{{--        </button>--}}
{{--    </x-slot>--}}
    <x-form-column type="full">
        <x-input.text name="subject" required value="{!! $template->subject !!}"
                      description="Available placeholders: {{ App\Helpers\Helper::generatePhx('receiver_name') }}, {{ App\Helpers\Helper::generatePhx('receiver_email') }}."/>
    </x-form-column>
    <x-form-column type="full">
        <x-input.textarea name="message" required value="{!! $template->message !!}" label="Message"
                          rows="10"
                          description="Available placeholders: {{ App\Helpers\Helper::generatePhx('receiver_name') }}, {{ App\Helpers\Helper::generatePhx('receiver_email') }}. Inline attachments by name: {{ App\Helpers\Helper::generatePhx('attach-AttachName1') }}, {{ App\Helpers\Helper::generatePhx('attach-AttachName2') }} etc."/>
    </x-form-column>
    <x-form-column>
        <x-input.select name="managers[]" label="Managers" multiple data-live-search="true" data-size="5"
                        data-selected-text-format="count" :options="$managers_options ?? []"
                        :disabled="isset($direct)"/>
        <x-input.select name="advertisers[]" label="Advertisers" multiple data-live-search="true" data-size="5"
                        data-selected-text-format="count" :options="$advertisers_options ?? []"
                        :disabled="isset($direct)"/>
        <x-input.select name="publishers[]" label="Publishers" multiple data-live-search="true" data-size="5"
                        data-selected-text-format="count" :options="$publishers_options ?? []"
                        :disabled="isset($direct)"/>
    </x-form-column>
    <x-form-column>
        <x-input.textarea name="emails" label="Email Addresses"
                          description="Use semicolon for multiple addresses."
                          placeholder="name1@example.com; name2@example.com; etc."
                          onfocus="this.placeholder=''" 
                          onblur="this.placeholder='name1@example.com; name2@example.com; etc.'"
                          :disabled="isset($direct)"
                          value="" rows="6"/>
        <x-input.check name="send-copy" label="Send a copy to me"/>
    </x-form-column>
    <div class="col-sm-12 col-md-6">
        @foreach($template->attachments as $attachment)
            <div class="form-group attach">
                <div class=" input-group">
                    <button type="button" onclick="Ads.Modules.EmailsTemplates.attachments.removeFixed(this)"
                            class="btn btn-outline-danger py-0 px-1 mr-2">
                        <i class="material-icons">clear</i>
                    </button>
                    Attachment: {{ $attachment->name }}{{ $attachment->inline ? ' (Inline)' : '' }}
                    <input type="hidden" name="template_attachments[]" value="{{ $attachment->id }}">
                </div>
            </div>
        @endforeach
    </div>
    <x-form-column type="full">
        <button type="button" class="btn btn-outline-dark p-1"
                onclick="Ads.Modules.EmailsTemplates.attachments.add(this)">
            <i class="material-icons">add_circle_outline</i> Add Attachment
        </button>
    </x-form-column>
    <x-form-column class="attach template d-none">
        <x-input.text name="attachment-name" label="Name" description="Identifier for using in a message."/>
    </x-form-column>
    <x-form-column class="attach template d-none">
        <div class="form-group">
            <label for="attachment-file" class="col-form-label text-md-right">File</label>
            <div class="input-group">
                <input class="form-control" type="file" id="attachment-file" name="attachment-file">
                <button type="button" class="btn btn-outline-danger py-0 px-3 ml-4"
                        onclick="Ads.Modules.EmailsTemplates.attachments.remove(this)">
                    <i class="material-icons">clear</i>
                </button>
            </div>
        </div>
        <x-input.check name="attachment-inline" label="Inline"/>
    </x-form-column>
</x-form>
