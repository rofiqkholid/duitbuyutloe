@props([
    'name',
    'id' => null,
    'selected' => null,
    'placeholder' => 'Pilih opsi...',
    'options' => [],
    'required' => false,
])

@php
    $selectId = $id ?? 'select_' . $name . '_' . \Illuminate\Support\Str::random(5);
    $currentVal = (string) old($name, $selected ?? '');

    $normalizedOptions = [];
    foreach ($options as $key => $data) {
        $valStr = (string) $key;
        if (is_array($data)) {
            $normalizedOptions[$valStr] = [
                'label' => $data['label'] ?? $valStr,
                'icon'  => $data['icon'] ?? '',
                'desc'  => $data['desc'] ?? null,
            ];
        } else {
            $raw = (string) $data;
            $icon = '';
            $label = $raw;
            if (preg_match('/^(\X\s*)(.+)$/u', $raw, $matches) && preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $matches[1])) {
                $icon = trim($matches[1]);
                $label = trim($matches[2]);
            }
            $normalizedOptions[$valStr] = [
                'label' => $label,
                'icon'  => $icon,
                'desc'  => null,
            ];
        }
    }

    if ($currentVal === '' && !empty($normalizedOptions)) {
        $firstKey = array_key_first($normalizedOptions);
        $currentVal = (string) $firstKey;
    }

    $activeOption = $normalizedOptions[$currentVal] ?? null;
@endphp

<div class="relative w-full custom-select-component" id="{{ $selectId }}_wrapper" data-select-id="{{ $selectId }}">
    <!-- Hidden input for standard form submit & validation -->
    <input 
        type="hidden" 
        name="{{ $name }}" 
        id="{{ $selectId }}_input" 
        value="{{ $currentVal }}" 
        {{ $required ? 'required' : '' }}
    >

    <!-- Trigger Button -->
    <button
        type="button"
        id="{{ $selectId }}_btn"
        aria-haspopup="listbox"
        aria-expanded="false"
        class="w-full h-11 flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 hover:border-slate-400 focus:outline-none focus:border-slate-400 transition-all text-left outline-none group cursor-pointer"
    >
        <div class="flex items-center gap-2.5 min-w-0 pr-2">
            <span class="custom-select-trigger-icon text-base leading-none flex-shrink-0 {{ empty($activeOption['icon']) ? 'hidden' : '' }}">
                {{ $activeOption['icon'] ?? '' }}
            </span>
            <span class="custom-select-trigger-label text-sm font-medium text-slate-800 truncate">
                {{ $activeOption['label'] ?? $placeholder }}
            </span>
        </div>

        <svg 
            class="custom-select-chevron w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0" 
            viewBox="0 0 20 20" 
            fill="currentColor"
        >
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>

    <!-- Dropdown Options Menu -->
    <div
        id="{{ $selectId }}_menu"
        role="listbox"
        class="custom-select-menu hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-lg shadow-slate-900/5 p-1.5 z-50 max-h-64 overflow-y-auto"
    >
        @foreach($normalizedOptions as $key => $opt)
            @php $isSelected = ($key === $currentVal); @endphp
            <button
                type="button"
                role="option"
                aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                data-value="{{ $key }}"
                data-label="{{ $opt['label'] }}"
                data-icon="{{ $opt['icon'] }}"
                class="custom-select-option w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors text-left cursor-pointer {{ $isSelected ? 'bg-slate-100 text-slate-900 font-medium' : 'text-slate-700 hover:bg-slate-50 font-normal' }}"
            >
                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                    @if(!empty($opt['icon']))
                        <span class="text-base leading-none flex-shrink-0">{{ $opt['icon'] }}</span>
                    @endif
                    <div class="truncate">
                        <span class="block truncate">{{ $opt['label'] }}</span>
                        @if(!empty($opt['desc']))
                            <span class="block text-xs text-slate-400 font-normal truncate">{{ $opt['desc'] }}</span>
                        @endif
                    </div>
                </div>
            </button>
        @endforeach
    </div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initCustomSelects() {
            document.querySelectorAll('.custom-select-component').forEach(function (wrapper) {
                if (wrapper.dataset.initialized) return;
                wrapper.dataset.initialized = 'true';

                const selectId = wrapper.dataset.selectId;
                const btn = document.getElementById(selectId + '_btn');
                const menu = document.getElementById(selectId + '_menu');
                const input = document.getElementById(selectId + '_input');
                const chevron = btn.querySelector('.custom-select-chevron');
                const triggerIcon = btn.querySelector('.custom-select-trigger-icon');
                const triggerLabel = btn.querySelector('.custom-select-trigger-label');
                const options = menu.querySelectorAll('.custom-select-option');

                function openMenu() {
                    document.querySelectorAll('.custom-select-menu').forEach(m => {
                        if (m !== menu) {
                            m.classList.add('hidden');
                            const otherBtn = m.previousElementSibling;
                            if (otherBtn) {
                                otherBtn.setAttribute('aria-expanded', 'false');
                                const otherChev = otherBtn.querySelector('.custom-select-chevron');
                                if (otherChev) otherChev.classList.remove('rotate-180');
                            }
                        }
                    });

                    menu.classList.remove('hidden');
                    btn.setAttribute('aria-expanded', 'true');
                    chevron.classList.add('rotate-180');
                }

                function closeMenu() {
                    menu.classList.add('hidden');
                    btn.setAttribute('aria-expanded', 'false');
                    chevron.classList.remove('rotate-180');
                }

                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (menu.classList.contains('hidden')) {
                        openMenu();
                    } else {
                        closeMenu();
                    }
                });

                options.forEach(function (opt) {
                    opt.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const val = opt.dataset.value;
                        const label = opt.dataset.label;
                        const icon = opt.dataset.icon;

                        // Update input value
                        input.value = val;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        input.dispatchEvent(new Event('input', { bubbles: true }));

                        // Update trigger presentation
                        triggerLabel.textContent = label;
                        if (icon) {
                            triggerIcon.textContent = icon;
                            triggerIcon.classList.remove('hidden');
                        } else {
                            triggerIcon.textContent = '';
                            triggerIcon.classList.add('hidden');
                        }

                        // Update active state in menu
                        options.forEach(function (item) {
                            const isMatch = (item.dataset.value === val);
                            item.setAttribute('aria-selected', isMatch ? 'true' : 'false');
                            if (isMatch) {
                                item.className = item.className
                                    .replace('hover:bg-slate-50', '')
                                    .replace('text-slate-700', '')
                                    .replace('font-normal', '')
                                    + ' bg-slate-100 text-slate-900 font-medium';
                            } else {
                                item.className = item.className
                                    .replace('bg-slate-100', '')
                                    .replace('text-slate-900', '')
                                    .replace('font-medium', '')
                                    + ' text-slate-700 hover:bg-slate-50 font-normal';
                            }
                        });

                        closeMenu();
                    });
                });
            });

            // Global click outside to close
            document.addEventListener('click', function (e) {
                document.querySelectorAll('.custom-select-component').forEach(function (wrapper) {
                    if (!wrapper.contains(e.target)) {
                        const selectId = wrapper.dataset.selectId;
                        const menu = document.getElementById(selectId + '_menu');
                        const btn = document.getElementById(selectId + '_btn');
                        if (menu && !menu.classList.contains('hidden')) {
                            menu.classList.add('hidden');
                            btn.setAttribute('aria-expanded', 'false');
                            const chev = btn.querySelector('.custom-select-chevron');
                            if (chev) chev.classList.remove('rotate-180');
                        }
                    }
                });
            });

            // Close on escape
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.custom-select-menu').forEach(function (menu) {
                        menu.classList.add('hidden');
                    });
                    document.querySelectorAll('.custom-select-component button[aria-expanded="true"]').forEach(function (btn) {
                        btn.setAttribute('aria-expanded', 'false');
                        const chev = btn.querySelector('.custom-select-chevron');
                        if (chev) chev.classList.remove('rotate-180');
                    });
                }
            });
        }

        initCustomSelects();
    });
</script>
@endonce
