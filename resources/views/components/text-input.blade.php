@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-200 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-siakad-primary focus:border-siakad-primary transition-all duration-200 outline-none placeholder-gray-400 dark:placeholder-gray-500 font-medium sm:text-sm shadow-sm']) }}>
