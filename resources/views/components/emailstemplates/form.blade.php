<x-form name="add" method="POST" enctype="multipart/form-data">
    @isset($template)
        <x-slot name="action">{{ route('admin.emails-templates.update', ['template' => $template], false) }}</x-slot>
        <x-slot name="method">PUT</x-slot>
        <x-slot name="submit">Ads.item.submitForm(this)</x-slot>
    @else
        <x-slot name="action">{{ route('admin.emails-templates.store', absolute: false) }}</x-slot>
        <x-slot name="submit">Ads.list.submitAddForm(this)</x-slot>
    @endisset
    <x-form-column>
        <x-input.text name="title" required value="{{ isset($template) ? $template->title : '' }}"/>
    </x-form-column>
    <x-form-column>
        <x-input.text name="subject" required value="{!! isset($template) ? $template->subject : '' !!}"
                      />
    </x-form-column>
    <x-form-column type="full">
        <x-input.textarea name="message" required value="{!! isset($template) ? $template->message : '' !!}"
                          label="Message" rows="10"
                          />
    </x-form-column>
    @isset($template)
        @foreach($template->attachments as $attachment)
            <x-form-column class="attach">
                <x-input.text name="attachment[{{ $attachment->id }}][name]" label="Name"
                              data-index="{{ $attachment->id }}"
                              description="Identifier for using in a message."
                              value="{{$attachment->name}}"/>
            </x-form-column>
            <x-form-column class="attach">
                <div class="form-group">
                    <label for="attachment[{{ $attachment->id }}][file]"
                           class="col-form-label text-md-right">File</label>
                    <div class="input-group">
                        <input class="form-control" type="file" id="attachment[{{ $attachment->id }}][file]"
                               data-index="{{ $attachment->id }}"
                               name="attachment[{{ $attachment->id }}][file]">
                        <button type="button" class="btn btn-outline-danger py-0 px-3 ml-4"
                                onclick="Ads.Modules.EmailsTemplates.attachments.remove(this)">
                            <i class="material-icons">clear</i>
                        </button>
                    </div>
                </div>
                <x-input.check name="attachment[{{ $attachment->id }}][inline]" label="Inline"
                               data-index="{{ $attachment->id }}"
                               :checked="$attachment->inline" 
                               suffix="{{ $template->id }}_{{ $loop->index }}"/>
            </x-form-column>
        @endforeach
    @endisset
    <x-form-column type="full">
        <button type="button" class="btn btn-outline-dark p-1"
                onclick="Ads.Modules.EmailsTemplates.attachments.add(this)">
            <i class="material-icons">add_circle_outline</i> Add Attachment
        </button>
    </x-form-column>
    <x-form-column class="attach template d-none">
        <x-input.text name="attachment-name" label="Name" description="Identifier for using in message"/>
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
