<div id="create-message" class="card position-fixed right-3 bottom-15px mw-md-50 wmax-sm-500 wmin-300 display-none">
    <div class="card-body">
        <form action="{{ route('message_threads.store') }}" method="post">
            {{ csrf_field() }}
            <div>
                <!-- Subject Form Input -->
                <div class="form-group">
                    <label class="control-label">Subject</label>
                    <input type="text" class="form-control" name="subject" placeholder="Subject" value="{{ old('subject') }}">
                </div>

                <!-- Message Form Input -->
                <div class="form-group">
                    <label class="control-label">Message</label>
                    <textarea name="message" class="form-control" placeholder="type a message here...">{{ old('message') }}</textarea>
                </div>

                @if($users->isNotEmpty())
                <div>
                    <label class="control-label">Recipients</label>
                    <select id="recipients-ids" name="recipients[]" onchange="toggleElementDisabledState(this.value, '#user_roles-ids')" multiple="multiple" data-placeholder="select recipient(s)" class="form-control select-search">
                        @foreach($users as $user)
                        <option @selected(in_array($user->id, old('recipients', []))) value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <h6 class="text-info mb-0 mt-2">Or</h6>

                <div>
                    <label class="control-label">Broadcast to user role group(s) instead</label>
                    <select id="user_roles-ids" name="user_roles[]" onchange="toggleElementDisabledState(this.value, '#recipients-ids');" multiple="multiple" data-placeholder="select recipient(s)" class="form-control select">
                        @foreach($user_roles as $role)
                        @if($role->name == config('roles.default_roles.tenant.student'))
                        <option disabled value="{{ $role->name }}">{{ Str::plural($role->name) }}</option>
                        <option @selected(in_array($role->name, old('user_roles', []))) value="students_active">&xrArr; Active</option>
                        <option @selected(in_array($role->name, old('user_roles', []))) value="students_graduated">&xrArr; Graduated</option>
                        <option @selected(in_array($role->name, old('user_roles', []))) value="students">&xrArr; All</option>
                        @continue
                        @endif
                        <option @selected(in_array($role->name, old('user_roles', []))) value="{{ $role->name }}">{{ Str::plural($role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Form Input -->
                <div class="form-group mt-3 mb-0 w-100 d-flex space-btn">
                    <button type="submit" class="btn btn-primary w-fit">Create & Send <i class="material-symbols-rounded ml-1">send</i></button>
                    <button type="button" onclick="hideFormCard()" class="btn btn-secondary w-fit">Close </button>
                </div>
            </div>
        </form>
    </div>
</div>
