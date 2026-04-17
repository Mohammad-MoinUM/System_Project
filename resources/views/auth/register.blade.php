@component('layouts.auth')
    <div class="card lg:card-side lg:items-stretch bg-base-100 shadow-xl w-full max-w-5xl overflow-hidden">
        <div class="card-body p-8 md:p-10 lg:w-1/2">
            <div class="text-center md:text-left mb-8">
                <h1 class="text-3xl md:text-4xl font-semibold text-base-content mb-2">Create your account</h1>
                <p class="text-base-content/70">Join HaalChaal to manage services and bookings</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
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

                <div class="space-y-3">
                    <p class="text-sm font-medium text-base-content">Register as</p>
                    
                    <div class="grid grid-cols-1 gap-2">
                        <label class="card card-compact cursor-pointer border-2 border-base-300 p-4 hover:border-primary transition">
                            <div class="flex items-start">
                                <input
                                    type="radio"
                                    name="registration_type"
                                    value="individual"
                                    class="radio radio-primary mt-1"
                                    checked
                                />
                                <div class="ml-3">
                                    <p class="font-semibold text-base-content">Individual</p>
                                    <p class="text-sm text-base-content/70">Sign up as a customer or service provider</p>
                                </div>
                            </div>
                        </label>

                        <label class="card card-compact cursor-pointer border-2 border-base-300 p-4 hover:border-primary transition">
                            <div class="flex items-start">
                                <input
                                    type="radio"
                                    name="registration_type"
                                    value="corporate"
                                    class="radio radio-primary mt-1"
                                />
                                <div class="ml-3">
                                    <p class="font-semibold text-base-content">Corporate Client</p>
                                    <p class="text-sm text-base-content/70">Register your company for bulk service requests</p>
                                </div>
                            </div>
                        </label>
                    </div>

                    <script>
                        document.querySelectorAll('input[name="registration_type"]').forEach(radio => {
                            radio.addEventListener('change', function() {
                                if (this.value === 'corporate') {
                                    window.location.href = '{{ route('corporate.register') }}';
                                }
                            });
                        });
                    </script>
                </div>

                <div class="space-y-3" id="individual-role">
                    <p class="text-sm font-medium text-base-content">Role</p>
                    <div class="join w-full">
                        <input
                            class="btn join-item w-1/2"
                            type="radio"
                        name="role"
                            value="customer"
                            aria-label="Customer"
                            checked
                        />
                        <input
                            class="btn join-item w-1/2"
                            type="radio"
                            name="role"
                            value="provider"
                            aria-label="Provider"
                        />
                    </div>
                </div>

                <div class="form-control">
                    <label for="name" class="label">
                        <span class="label-text">Name</span>
                    </label>
                    <input id="name" name="name" type="text" required class="input input-bordered w-full">
                </div>

                <div class="form-control">
                    <label for="email" class="label">
                        <span class="label-text">Email Address</span>
                    </label>
                    <input id="email" name="email" type="email" required class="input input-bordered w-full" placeholder="you@example.com">
                </div>

                <div class="form-control">
                    <label for="referral_code" class="label">
                        <span class="label-text">Referral Code <span class="text-base-content/40">(optional)</span></span>
                    </label>
                    <input id="referral_code" name="referral_code" type="text" class="input input-bordered w-full" value="{{ old('referral_code') }}" placeholder="Enter a friend's code">
                    @error('referral_code')
                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-control">
                    <label for="password" class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <input id="password" name="password" type="password" required class="input input-bordered w-full">
                </div>

                <div class="form-control">
                    <label for="password_confirmation" class="label">
                        <span class="label-text">Confirm Password</span>
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="input input-bordered w-full">
                </div>

                <button type="submit" class="btn btn-primary w-full">Register</button>

                <p class="text-center text-sm text-base-content/70">
                    Already have an account?
                    <a href="{{ route('login') }}" class="link link-primary">Login</a>
                </p>
            </form>
        </div>

        <figure class="hidden lg:block lg:w-1/2 min-h-[420px]">
            <img src="{{ asset('images/register.jpg') }}" alt="Register"  fetchpriority="high" class="h-full w-full object-cover" />
        </figure>
    </div>
@endcomponent