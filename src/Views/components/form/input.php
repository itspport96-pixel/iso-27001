<?php
/**
 * Input Component
 * 
 * Uso:
 * include __DIR__ . '/components/form/input.php';
 * echo renderInput('email', 'text', 'Correo electrónico', $value, ['required' => true]);
 * echo renderInputWithIcon('email', 'text', 'Email', $value, $emailIcon);
 * 
 * @param string $name - Nombre del input
 * @param string $type - Tipo de input
 * @param string $label - Label del input
 * @param string $value - Valor actual
 * @param array $options - Opciones adicionales
 * @return string HTML del input
 */

function renderInput(string $name, string $type, string $label, string $value = '', array $options = []): string
{
    $defaults = [
        'required' => false,
        'placeholder' => '',
        'disabled' => false,
        'readonly' => false,
        'error' => '',
        'help' => '',
        'class' => '',
        'autocomplete' => '',
        'min' => '',
        'max' => '',
        'step' => '',
        'pattern' => '',
    ];
    
    $config = array_merge($defaults, $options);
    
    $id = 'input_' . $name;
    $inputClasses = 'block w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 sm:text-sm';
    
    if ($config['error']) {
        $inputClasses .= ' border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500';
    } else {
        $inputClasses .= ' border-gray-300';
    }
    
    if ($config['disabled']) {
        $inputClasses .= ' bg-gray-100 cursor-not-allowed';
    }
    
    if ($config['class']) {
        $inputClasses .= ' ' . $config['class'];
    }
    
    $attributes = sprintf(
        'type="%s" name="%s" id="%s" value="%s"',
        htmlspecialchars($type),
        htmlspecialchars($name),
        $id,
        htmlspecialchars($value)
    );
    
    if ($config['required']) $attributes .= ' required';
    if ($config['disabled']) $attributes .= ' disabled';
    if ($config['readonly']) $attributes .= ' readonly';
    if ($config['placeholder']) $attributes .= sprintf(' placeholder="%s"', htmlspecialchars($config['placeholder']));
    if ($config['autocomplete']) $attributes .= sprintf(' autocomplete="%s"', htmlspecialchars($config['autocomplete']));
    if ($config['min']) $attributes .= sprintf(' min="%s"', htmlspecialchars($config['min']));
    if ($config['max']) $attributes .= sprintf(' max="%s"', htmlspecialchars($config['max']));
    if ($config['step']) $attributes .= sprintf(' step="%s"', htmlspecialchars($config['step']));
    if ($config['pattern']) $attributes .= sprintf(' pattern="%s"', htmlspecialchars($config['pattern']));
    
    $requiredMark = $config['required'] ? '<span class="text-red-500 ml-1">*</span>' : '';
    
    $errorHtml = '';
    if ($config['error']) {
        $errorHtml = sprintf(
            '<p class="mt-2 text-sm text-red-600">%s</p>',
            htmlspecialchars($config['error'])
        );
    }
    
    $helpHtml = '';
    if ($config['help']) {
        $helpHtml = sprintf(
            '<p class="mt-2 text-sm text-gray-500">%s</p>',
            htmlspecialchars($config['help'])
        );
    }
    
    return sprintf(
        '<div>
            <label for="%s" class="block text-sm font-medium text-gray-700 mb-2">
                %s%s
            </label>
            <input %s class="%s">
            %s
            %s
        </div>',
        $id,
        htmlspecialchars($label),
        $requiredMark,
        $attributes,
        $inputClasses,
        $errorHtml,
        $helpHtml
    );
}

function renderInputWithIcon(string $name, string $type, string $label, string $value = '', string $icon = '', array $options = []): string
{
    $defaults = [
        'required' => false,
        'placeholder' => '',
        'disabled' => false,
        'readonly' => false,
        'error' => '',
        'help' => '',
        'autocomplete' => '',
        'icon_position' => 'left',
    ];
    
    $config = array_merge($defaults, $options);
    
    $id = 'input_' . $name;
    $inputClasses = 'block w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 sm:text-sm';
    
    if ($config['icon_position'] === 'left') {
        $inputClasses .= ' pl-10';
    } else {
        $inputClasses .= ' pr-10';
    }
    
    if ($config['error']) {
        $inputClasses .= ' border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500';
    } else {
        $inputClasses .= ' border-gray-300';
    }
    
    if ($config['disabled']) {
        $inputClasses .= ' bg-gray-100 cursor-not-allowed';
    }
    
    $attributes = sprintf(
        'type="%s" name="%s" id="%s" value="%s"',
        htmlspecialchars($type),
        htmlspecialchars($name),
        $id,
        htmlspecialchars($value)
    );
    
    if ($config['required']) $attributes .= ' required';
    if ($config['disabled']) $attributes .= ' disabled';
    if ($config['readonly']) $attributes .= ' readonly';
    if ($config['placeholder']) $attributes .= sprintf(' placeholder="%s"', htmlspecialchars($config['placeholder']));
    if ($config['autocomplete']) $attributes .= sprintf(' autocomplete="%s"', htmlspecialchars($config['autocomplete']));
    
    $requiredMark = $config['required'] ? '<span class="text-red-500 ml-1">*</span>' : '';
    
    $iconPosition = $config['icon_position'] === 'left' ? 'left-0 pl-3' : 'right-0 pr-3';
    $iconHtml = sprintf(
        '<div class="absolute inset-y-0 %s flex items-center pointer-events-none">%s</div>',
        $iconPosition,
        $icon
    );
    
    $errorHtml = '';
    if ($config['error']) {
        $errorHtml = sprintf(
            '<p class="mt-2 text-sm text-red-600">%s</p>',
            htmlspecialchars($config['error'])
        );
    }
    
    $helpHtml = '';
    if ($config['help']) {
        $helpHtml = sprintf(
            '<p class="mt-2 text-sm text-gray-500">%s</p>',
            htmlspecialchars($config['help'])
        );
    }
    
    return sprintf(
        '<div>
            <label for="%s" class="block text-sm font-medium text-gray-700 mb-2">
                %s%s
            </label>
            <div class="relative">
                %s
                <input %s class="%s">
            </div>
            %s
            %s
        </div>',
        $id,
        htmlspecialchars($label),
        $requiredMark,
        $iconHtml,
        $attributes,
        $inputClasses,
        $errorHtml,
        $helpHtml
    );
}

function renderPasswordInput(string $name, string $label, string $value = '', array $options = []): string
{
    $id = 'input_' . $name;
    
    $defaults = [
        'required' => false,
        'placeholder' => '',
        'error' => '',
        'help' => '',
        'show_toggle' => true,
        'autocomplete' => 'current-password',
    ];
    
    $config = array_merge($defaults, $options);
    
    $inputClasses = 'block w-full pl-10 pr-10 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 sm:text-sm';
    
    if ($config['error']) {
        $inputClasses .= ' border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500';
    } else {
        $inputClasses .= ' border-gray-300';
    }
    
    $attributes = sprintf(
        'type="password" name="%s" id="%s" value="%s"',
        htmlspecialchars($name),
        $id,
        htmlspecialchars($value)
    );
    
    if ($config['required']) $attributes .= ' required';
    if ($config['placeholder']) $attributes .= sprintf(' placeholder="%s"', htmlspecialchars($config['placeholder']));
    if ($config['autocomplete']) $attributes .= sprintf(' autocomplete="%s"', htmlspecialchars($config['autocomplete']));
    
    $requiredMark = $config['required'] ? '<span class="text-red-500 ml-1">*</span>' : '';
    
    $toggleButton = '';
    if ($config['show_toggle']) {
        $toggleButton = sprintf(
            '<button type="button" onclick="togglePassword(\'%s\')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg id="%s-show" class="h-5 w-5 text-gray-400 hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg id="%s-hide" class="hidden h-5 w-5 text-gray-400 hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
            </button>',
            $id,
            $id,
            $id
        );
    }
    
    $errorHtml = '';
    if ($config['error']) {
        $errorHtml = sprintf(
            '<p class="mt-2 text-sm text-red-600">%s</p>',
            htmlspecialchars($config['error'])
        );
    }
    
    $helpHtml = '';
    if ($config['help']) {
        $helpHtml = sprintf(
            '<p class="mt-2 text-sm text-gray-500">%s</p>',
            htmlspecialchars($config['help'])
        );
    }
    
    $script = '';
    if ($config['show_toggle']) {
        $script = '<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const showIcon = document.getElementById(inputId + "-show");
    const hideIcon = document.getElementById(inputId + "-hide");
    
    if (input.type === "password") {
        input.type = "text";
        showIcon.classList.add("hidden");
        hideIcon.classList.remove("hidden");
    } else {
        input.type = "password";
        showIcon.classList.remove("hidden");
        hideIcon.classList.add("hidden");
    }
}
</script>';
    }
    
    return sprintf(
        '<div>
            <label for="%s" class="block text-sm font-medium text-gray-700 mb-2">
                %s%s
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input %s class="%s">
                %s
            </div>
            %s
            %s
            %s
        </div>',
        $id,
        htmlspecialchars($label),
        $requiredMark,
        $attributes,
        $inputClasses,
        $toggleButton,
        $errorHtml,
        $helpHtml,
        $script
    );
}
