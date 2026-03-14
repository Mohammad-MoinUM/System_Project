

@php
    $currentStep = $currentStep ?? 1;
    $totalSteps  = 3;
    $progress    = round(($currentStep / $totalSteps) * 100);

    $steps = [
        1 => [
            'label' => 'Basic Info',
            'icon'  => 'heroicon-o-user',
        ],
        2 => [
            'label' => 'Professional',
            'icon'  => 'heroicon-o-document-text',
        ],
        3 => [
            'label' => 'Location',
            'icon'  => 'heroicon-o-globe-alt',
        ],
    ];
@endphp

{{-- Title --}}
<div class="text-center mb-8">
    <h1 class="text-3xl font-extrabold text-base-content tracking-tight">
        Complete Your Profile 🎉
    </h1>
    <p class="text-base-content/50 mt-2 text-sm">
        Just a few details to get you started on the platform.
    </p>
</div>

{{-- Progress Bar --}}
<div class="mb-6">
    <div class="flex items-center justify-between mb-1">
        <span class="text-xs font-semibold text-base-content/60">Profile Completion</span>
        <span class="text-xs font-bold text-base-content">{{ $progress }}%</span>
    </div>
    <div class="w-full bg-base-300 rounded-full h-2">
        <div class="bg-base-content h-2 rounded-full transition-all duration-500"
             style="width: {{ $progress }}%"></div>
    </div>
</div>

{{-- Step Indicators --}}
<div class="flex items-center justify-center mb-8 select-none">
    @foreach ($steps as $step => $info)

        {{-- Step Circle --}}
        <div class="flex flex-col items-center">
            <div class="w-11 h-11 rounded-full flex items-center justify-center border-2 transition-all duration-300
                @if ($step < $currentStep)
                    bg-base-content border-base-content text-base-100
                @elseif ($step === $currentStep)
                    bg-base-content border-base-content text-base-100
                @else
                    bg-base-100 border-base-300 text-base-content/30
                @endif">

                @if ($step < $currentStep)
                    {{-- Completed: checkmark --}}
                    <x-heroicon-o-check class="w-5 h-5" />
                @else
                    {{-- Icon --}}
                    <x-dynamic-component :component="$info['icon']" class="w-5 h-5" />
                @endif
            </div>
            <span class="text-xs mt-2 font-medium
                @if ($step === $currentStep) text-base-content font-semibold
                @else text-base-content/40
                @endif">
                {{ $info['label'] }}
            </span>
        </div>

        {{-- Connector Line (not after last step) --}}
        @if ($step < $totalSteps)
            <div class="flex-1 h-px mx-3 mb-5
                @if ($step < $currentStep) bg-base-content/60
                @else bg-base-300
                @endif">
            </div>
        @endif

    @endforeach
</div>
