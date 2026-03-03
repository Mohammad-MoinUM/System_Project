@component('layouts.auth')
    <div class="card lg:card-side lg:items-stretch bg-base-100 shadow-xl w-full max-w-5xl overflow-hidden">
        <div class="card-body p-8 md:p-10 lg:w-1/2">
            <div class="text-center md:text-left mb-8">
                <h1 class="text-3xl md:text-4xl font-semibold text-base-content mb-2">Create your account</h1>
                <p class="text-base-content/70">Join HaalChaal to manage services and bookings</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <div class="space-y-3">
                    <p class="text-sm font-medium text-base-content">Register as</p>
                    <div class="join w-full">
                        <input
                            class="btn join-item w-1/2"
                            type="radio"
                        name="role"
                            value="taker"
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
                    <label for="password" class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <input id="password" name="password" type="password" required class="input input-bordered w-full">
                </div>

                <button type="submit" class="btn btn-primary w-full">Register</button>

                <p class="text-center text-sm text-base-content/70">
                    Already have an account?
                    <a href="{{ route('login') }}" class="link link-primary">Login</a>
                </p>
            </form>
        </div>

        <figure class="hidden lg:block lg:w-1/2 min-h-[420px]">
            <img src="{{ asset('images/register.jpg') }}" alt="Register" loading="eager" fetchpriority="high" class="h-full w-full object-cover" />
        </figure>
    </div>
@endcomponent