@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-[#CFE8E1] focus:border-[#1C8C74] focus:ring-[#1C8C74] rounded-[9px] shadow-sm']) }}>
