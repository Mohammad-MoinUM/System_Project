@component('layouts.auth')
    <div class="card lg:card-side lg:items-stretch bg-base-100 shadow-xl w-full max-w-6xl overflow-hidden">
        <div class="card-body p-8 md:p-10 lg:w-1/2">
            <div class="text-center md:text-left mb-8">
                <h1 class="text-3xl md:text-4xl font-semibold text-base-content mb-2">Register Your Company</h1>
                <p class="text-base-content/70">Join HaalChaal B2B to manage services across multiple branches</p>
            </div>

            <form method="POST" action="{{ route('corporate.register.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-error">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Company Information -->
                <div class="divider">Company Details</div>

                <div class="form-control">
                    <label for="company_name" class="label">
                        <span class="label-text">Company Name *</span>
                    </label>
                    <input id="company_name" name="company_name" type="text" required class="input input-bordered w-full" value="{{ old('company_name') }}">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label for="email" class="label">
                            <span class="label-text">Company Email *</span>
                        </label>
                        <input id="email" name="email" type="email" required class="input input-bordered w-full" placeholder="company@example.com" value="{{ old('email') }}">
                    </div>

                    <div class="form-control">
                        <label for="phone" class="label">
                            <span class="label-text">Phone *</span>
                        </label>
                        <input id="phone" name="phone" type="tel" required class="input input-bordered w-full" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="form-control">
                    <label for="address" class="label">
                        <span class="label-text">Address *</span>
                    </label>
                    <input id="address" name="address" type="text" required class="input input-bordered w-full" value="{{ old('address') }}">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label for="city" class="label">
                            <span class="label-text">City *</span>
                        </label>
                        <input id="city" name="city" type="text" required class="input input-bordered w-full" value="{{ old('city') }}">
                    </div>

                    <div class="form-control">
                        <label for="postal_code" class="label">
                            <span class="label-text">Postal Code</span>
                        </label>
                        <input id="postal_code" name="postal_code" type="text" class="input input-bordered w-full" value="{{ old('postal_code') }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label for="contact_person_name" class="label">
                            <span class="label-text">Contact Person Name *</span>
                        </label>
                        <input id="contact_person_name" name="contact_person_name" type="text" required class="input input-bordered w-full" value="{{ old('contact_person_name') }}">
                    </div>

                    <div class="form-control">
                        <label for="company_registration_number" class="label">
                            <span class="label-text">Company Reg. Number *</span>
                        </label>
                        <input id="company_registration_number" name="company_registration_number" type="text" required class="input input-bordered w-full" value="{{ old('company_registration_number') }}">
                    </div>
                </div>

                <div class="form-control">
                    <label for="company_documents" class="label">
                        <span class="label-text">Company Documents (Optional)</span>
                    </label>
                    <input id="company_documents" name="company_documents" type="file" class="file-input file-input-bordered w-full">
                </div>

                <!-- Admin Account -->
                <div class="divider">Admin Account</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label for="first_name" class="label">
                            <span class="label-text">First Name *</span>
                        </label>
                        <input id="first_name" name="first_name" type="text" required class="input input-bordered w-full" value="{{ old('first_name') }}">
                    </div>

                    <div class="form-control">
                        <label for="last_name" class="label">
                            <span class="label-text">Last Name *</span>
                        </label>
                        <input id="last_name" name="last_name" type="text" required class="input input-bordered w-full" value="{{ old('last_name') }}">
                    </div>
                </div>

                <div class="form-control">
                    <label for="password" class="label">
                        <span class="label-text">Password *</span>
                    </label>
                    <input id="password" name="password" type="password" required class="input input-bordered w-full">
                </div>

                <div class="form-control">
                    <label for="password_confirmation" class="label">
                        <span class="label-text">Confirm Password *</span>
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="input input-bordered w-full">
                </div>

                <button type="submit" class="btn btn-primary w-full">Register Company</button>

                <p class="text-center text-sm text-base-content/70">
                    Not registering as company?
                    <a href="{{ route('register') }}" class="link link-primary">Register as Individual</a>
                </p>
            </form>
        </div>

        <figure class="hidden lg:block lg:w-1/2 min-h-[600px]">
            <img src="{{ asset('images/register.jpg') }}" alt="Register"  fetchpriority="high" class="h-full w-full object-cover" />
        </figure>
    </div>
@endcomponent
