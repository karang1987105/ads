<x-form name="add" method="POST">
    <x-slot name="action">{{ route('admin.managers.change_permissions', compact('manager'), false) }}</x-slot>
    <x-slot name="method">PUT</x-slot>
    <x-slot name="submit">Ads.item.submitForm(this)</x-slot>

    <fieldset class="row col-12">
        <div class="form-check legend">
            <input type="checkbox" id="publishers" onchange="Ads.form.legendToggle(this)"
                    {{$manager->hasAllPermissions('publishers',\App\Models\UserManager::PERMISSIONS['publishers'])?'checked' : ''}}>
            <label for="publishers">Publishers</label>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="publishers[List]" label="List Existing"
                           :checked="$manager->hasPermission('publishers','List')"/>
            <x-input.check name="publishers[Create]" label="Create"
                           :checked="$manager->hasPermission('publishers','Create')"/>
            <x-input.check name="publishers[Update]" label="Edit"
                           :checked="$manager->hasPermission('publishers','Update')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="publishers[Delete]" label="Delete"
                           :checked="$manager->hasPermission('publishers','Delete')"/>
            <x-input.check name="publishers[Block]" label="Suspend"
                           :checked="$manager->hasPermission('publishers','Block')"/>
            <x-input.check name="publishers[Activate]" label="Approve"
                           :checked="$manager->hasPermission('publishers','Activate')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="publishers[Send Email]" label="Send Email"
                           :checked="$manager->hasPermission('publishers','Send Email')"/>
            <x-input.check name="publishers[Add Fund]" label="Add Funds"
                           :checked="$manager->hasPermission('publishers','Add Fund')"/>
            <x-input.check name="publishers[Remove Fund]" label="Remove Funds"
                           :checked="$manager->hasPermission('publishers','Remove Fund')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="publishers[Login Behalf]" label="Login Behalf"
                           :checked="$manager->hasPermission('publishers','Login Behalf')"/>
            <x-input.check name="publishers[Domains]" label="Domains"
                           :checked="$manager->hasPermission('publishers','Domains')"/>
            <x-input.check name="publishers[Places]" label="Places"
                           :checked="$manager->hasPermission('publishers','Places')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="publishers[Withdrawal Requests]" label="Withdrawal Requests"
                           :checked="$manager->hasPermission('publishers','Withdrawal Requests')"/>
        </div>
    </fieldset>

    <fieldset class="row col-12">
        <div class="form-check legend">
            <input type="checkbox" id="advertisers" onchange="Ads.form.legendToggle(this)"
                    {{$manager->hasAllPermissions('advertisers', \App\Models\UserManager::PERMISSIONS['advertisers'])?'checked' : ''}}>
            <label for="advertisers">Advertisers</label>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="advertisers[List]" label="List Existing"
                           :checked="$manager->hasPermission('advertisers','List')"/>
            <x-input.check name="advertisers[Create]" label="Create"
                           :checked="$manager->hasPermission('advertisers','Create')"/>
            <x-input.check name="advertisers[Update]" label="Edit"
                           :checked="$manager->hasPermission('advertisers','Update')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="advertisers[Delete]" label="Delete"
                           :checked="$manager->hasPermission('advertisers','Delete')"/>
            <x-input.check name="advertisers[Block]" label="Suspend"
                           :checked="$manager->hasPermission('advertisers','Block')"/>
            <x-input.check name="advertisers[Activate]" label="Approve"
                           :checked="$manager->hasPermission('advertisers','Activate')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="advertisers[Send Email]" label="Send Email"
                           :checked="$manager->hasPermission('advertisers','Send Email')"/>
            <x-input.check name="advertisers[Add Fund]" label="Add Funds"
                           :checked="$manager->hasPermission('advertisers','Add Fund')"/>
            <x-input.check name="advertisers[Remove Fund]" label="Remove Funds"
                           :checked="$manager->hasPermission('advertisers','Remove Fund')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="advertisers[Login Behalf]" label="Login Behalf"
                           :checked="$manager->hasPermission('advertisers','Login Behalf')"/>
            <x-input.check name="advertisers[Domains]" label="Domains"
                           :checked="$manager->hasPermission('advertisers','Domains')"/>
        </div>
    </fieldset>

    <fieldset class="row col-12">
        <div class="form-check legend">
            <input type="checkbox" id="advertisements" onchange="Ads.form.legendToggle(this)"
                    {{$manager->hasAllPermissions('advertisements',['Create', 'Update', 'Delete']) ? 'checked' : ''}}>
            <label for="advertisements">Advertisements</label>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="advertisements[Create]" label="Create"
                           :checked="$manager->hasPermission('advertisements','Create')"/>
            <x-input.check name="advertisements[Activate]" label="Approve"
                           :checked="$manager->hasPermission('advertisements','Activate')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="advertisements[Update]" label="Edit"
                           :checked="$manager->hasPermission('advertisements','Update')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="advertisements[Delete]" label="Delete"
                           :checked="$manager->hasPermission('advertisements','Delete')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="advertisements[Block]" label="Decline"
                           :checked="$manager->hasPermission('advertisements','Block')"/>
        </div>
    </fieldset>

    <fieldset class="row col-12">
        <div class="form-check legend">
            <input type="checkbox" id="promos" onchange="Ads.form.legendToggle(this)"
                    {{$manager->hasAllPermissions('promos',['Create', 'Update', 'Delete'])?'checked' : ''}}>
            <label for="promos">Promo Codes</label>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="promos[Create]" label="Create"
                           :checked="$manager->hasPermission('promos','Create')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="promos[Update]" label="Edit"
                           :checked="$manager->hasPermission('promos','Update')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="promos[Delete]" label="Delete"
                           :checked="$manager->hasPermission('promos','Delete')"/>
        </div>
    </fieldset>

    <fieldset class="row col-12 border-bottom-0">
	
	
	        <div class="form-check legend">
            <input type="checkbox" id="send_email" onchange="Ads.form.legendToggle(this)"
                    {{$manager->hasAllPermissions('send_email',['Create', 'Update', 'Delete'])?'checked' : ''}}>
            <label for="send_email">Email Templates</label>
        </div>
	
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="send_email[Create]" label="Create"
                           :checked="$manager->hasPermission('send_email','Create')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="send_email[Update]" label="Edit"
                           :checked="$manager->hasPermission('send_email','Update')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="send_email[Delete]" label="Delete"
                           :checked="$manager->hasPermission('send_email','Delete')"/>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
            <x-input.check name="send_email[Send]" label="Send"
                           :checked="$manager->hasPermission('send_email','Send')"/>
        </div>
    </fieldset>
</x-form>
