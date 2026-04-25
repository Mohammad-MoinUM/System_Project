@component('layouts.auth')
    <div class="card lg:card-side lg:items-stretch bg-base-100 shadow-xl w-full max-w-5xl overflow-hidden">
        <figure class="order-first block lg:order-last lg:w-1/2 h-48 sm:h-56 lg:h-auto lg:min-h-[420px]">
            <img src="{{ asset('images/login.jpg') }}" alt="Login" fetchpriority="high" class="h-full w-full object-cover" />
        </figure>

        <div class="card-body p-5 sm:p-8 md:p-10 lg:w-1/2">
            <div class="text-center md:text-left mb-6 sm:mb-8">
                <h1 class="text-3xl md:text-4xl font-semibold text-base-content mb-2">Welcome back</h1>
                <p class="text-base-content/70">Sign in to your HaalChaal account</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5 sm:space-y-6">
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
                    <p class="text-sm font-medium text-base-content">Sign in as</p>
                    <div class="join w-full">
                        <input
                            class="btn join-item flex-1"
                            type="radio"
                            name="role"
                            value="customer"
                            aria-label="Customer"
                            checked
                        />
                        <input
                            class="btn join-item flex-1"
                            type="radio"
                            name="role"
                            value="provider"
                            aria-label="Provider"
                        />
                        <input
                            class="btn join-item flex-1"
                            type="radio"
                            name="role"
                            value="admin"
                            aria-label="Admin"
                        />
                    </div>
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
                    @if (Route::has('password.request'))
                        <label class="label">
                            <span class="label-text-alt"></span>
                            <a href="#" class="label-text-alt link link-primary">Forgot password?</a>
                        </label>
                    @endif
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm" />
                        <span class="label-text">Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full h-11">Login</button>

                <p class="text-center text-sm text-base-content/70">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="link link-primary">Register</a>
                </p>
            </form>
        </div>


    </div>
@endcomponent