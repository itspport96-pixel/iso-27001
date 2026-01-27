<?php
/**
 * Textarea Component
 * 
 * Uso:
 * include __DIR__ . '/components/form/textarea.php';
 * echo renderTextarea('observaciones', 'Observaciones', $value, ['rows' => 5, 'required' => true]);
 * 
 * @param string $name - Nombre del textarea
 * @param string $label - Label del textarea
 * @param string $value - Valor actual
 * @param array $options - Opciones adicionales
 * @return string HTML del textarea
 */

function renderTextarea(string $name, string $label, string $value = '', array $options = []): string
{
    $defaults = [
        'required' => false,
        'placeholder' => '',
        'disabled' => false,
        'readonly' => false,
        'error' => '',
        'help' => '',
        'class' => '',
        'rows' => 4,
        'maxlength' => '',
        'show_counter' => false,
    ];
    
    $config = array_merge($defaults, $options);
    
    $id = 'textarea_' . $name;
    $textareaClasses = 'block w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 sm:text-sm';
    
    if ($config['error']) {
        $textareaClasses .= ' border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500';
    } else {
        $textareaClasses .= ' border-gray-300';
    }
    
    if ($config['disabled']) {
        $textareaClasses .= ' bg-gray-100 cursor-not-allowed';
    }
    
    if ($config['class']) {
        $textareaClasses .= ' ' . $config['class'];
    }
    
    $attributes = sprintf(
        'name="%s" id="%s" rows="%d"',
        htmlspecialchars($name),
        $id,
        $config['rows']
    );
    
    if ($config['required']) $attributes .= ' required';
    if ($config['disabled']) $attributes .= ' disabled';
    if ($config['readonly']) $attributes .= ' readonly';
    if ($config['placeholder']) $attributes .= sprintf(' placeholder="%s"', htmlspecialchars($config['placeholder']));
    if ($config['maxlength']) $attributes .= sprintf(' maxlength="%s"', htmlspecialchars($config['maxlength']));
    
    if ($config['show_counter'] && $config['maxlength']) {
        $attributes .= sprintf(' oninput="updateCounter(\'%s\', %d)"', $id, $config['maxlength']);
    }
    
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
    
    $counterHtml = '';
    if ($config['show_counter'] && $config['maxlength']) {
        $currentLength = mb_strlen($value);
        $counterHtml = sprintf(
            '<div class="mt-2 flex justify-end">
                <span class="text-sm text-gray-500">
                    <span id="%s-counter">%d</span> / %d caracteres
                </span>
            </div>',
            $id,
            $currentLength,
            $config['maxlength']
        );
    }
    
    $script = '';
    if ($config['show_counter'] && $config['maxlength']) {
        $script = '<script>
function updateCounter(textareaId, maxLength) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(textareaId + "-counter");
    if (textarea && counter) {
        counter.textContent = textarea.value.length;
    }
}
</script>';
    }
    
    return sprintf(
        '<div>
            <label for="%s" class="block text-sm font-medium text-gray-700 mb-2">
                %s%s
            </label>
            <textarea %s class="%s">%s</textarea>
            %s
            %s
            %s
            %s
        </div>',
        $id,
        htmlspecialchars($label),
        $requiredMark,
        $attributes,
        $textareaClasses,
        htmlspecialchars($value),
        $counterHtml,
        $errorHtml,
        $helpHtml,
        $script
    );
}

function renderCheckbox(string $name, string $label, bool $checked = false, array $options = []): string
{
    $defaults = [
        'required' => false,
        'disabled' => false,
        'error' => '',
        'help' => '',
        'value' => '1',
    ];
    
    $config = array_merge($defaults, $options);
    
    $id = 'checkbox_' . $name;
    
    $attributes = sprintf(
        'type="checkbox" name="%s" id="%s" value="%s"',
        htmlspecialchars($name),
        $id,
        htmlspecialchars($config['value'])
    );
    
    if ($config['required']) $attributes .= ' required';
    if ($config['disabled']) $attributes .= ' disabled';
    if ($checked) $attributes .= ' checked';
    
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
            '<p class="ml-7 text-sm text-gray-500">%s</p>',
            htmlspecialchars($config['help'])
        );
    }
    
    return sprintf(
        '<div>
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input %s class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="%s" class="font-medium text-gray-700">%s</label>
                </div>
            </div>
            %s
            %s
        </div>',
        $attributes,
        $id,
        htmlspecialchars($label),
        $helpHtml,
        $errorHtml
    );
}

function renderCheckboxGroup(string $name, string $label, array $options, array $selected = [], array $config = []): string
{
    $defaults = [
        'required' => false,
        'disabled' => false,
        'error' => '',
        'help' => '',
        'inline' => false,
    ];
    
    $settings = array_merge($defaults, $config);
    
    $requiredMark = $settings['required'] ? '<span class="text-red-500 ml-1">*</span>' : '';
    
    $containerClass = $settings['inline'] ? 'flex flex-wrap gap-4' : 'space-y-2';
    
    $optionsHtml = '';
    foreach ($options as $value => $optionLabel) {
        $id = 'checkbox_' . $name . '_' . $value;
        $isChecked = in_array($value, $selected) ? 'checked' : '';
        
        $attributes = sprintf(
            'type="checkbox" name="%s[]" id="%s" value="%s"',
            htmlspecialchars($name),
            $id,
            htmlspecialchars($value)
        );
        
        if ($settings['required']) $attributes .= ' required';
        if ($settings['disabled']) $attributes .= ' disabled';
        if ($isChecked) $attributes .= ' checked';
        
        $optionsHtml .= sprintf(
            '<div class="flex items-center">
                <input %s class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                <label for="%s" class="ml-2 block text-sm text-gray-900">%s</label>
            </div>',
            $attributes,
            $id,
            htmlspecialchars($optionLabel)
        );
    }
    
    $errorHtml = '';
    if ($settings['error']) {
        $errorHtml = sprintf(
            '<p class="mt-2 text-sm text-red-600">%s</p>',
            htmlspecialchars($settings['error'])
        );
    }
    
    $helpHtml = '';
    if ($settings['help']) {
        $helpHtml = sprintf(
            '<p class="mt-2 text-sm text-gray-500">%s</p>',
            htmlspecialchars($settings['help'])
        );
    }
    
    return sprintf(
        '<div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                %s%s
            </label>
            <div class="%s">%s</div>
            %s
            %s
        </div>',
        htmlspecialchars($label),
        $requiredMark,
        $containerClass,
        $optionsHtml,
        $errorHtml,
        $helpHtml
    );
}

function renderFileUpload(string $name, string $label, array $options = []): string
{
    $defaults = [
        'required' => false,
        'disabled' => false,
        'error' => '',
        'help' => '',
        'accept' => '',
        'multiple' => false,
        'show_preview' => false,
    ];
    
    $config = array_merge($defaults, $options);
    
    $id = 'file_' . $name;
    
    $attributes = sprintf(
        'type="file" name="%s" id="%s"',
        htmlspecialchars($name),
        $id
    );
    
    if ($config['required']) $attributes .= ' required';
    if ($config['disabled']) $attributes .= ' disabled';
    if ($config['accept']) $attributes .= sprintf(' accept="%s"', htmlspecialchars($config['accept']));
    if ($config['multiple']) $attributes .= ' multiple';
    
    if ($config['show_preview']) {
        $attributes .= sprintf(' onchange="previewFile(\'%s\')"', $id);
    }
    
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
    
    $previewHtml = '';
    if ($config['show_preview']) {
        $previewHtml = sprintf(
            '<div id="%s-preview" class="mt-4 hidden">
                <img id="%s-preview-img" class="h-32 w-32 object-cover rounded-lg border border-gray-300" alt="Preview">
            </div>',
            $id,
            $id
        );
    }
    
    $script = '';
    if ($config['show_preview']) {
        $script = '<script>
function previewFile(inputId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(inputId + "-preview");
    const previewImg = document.getElementById(inputId + "-preview-img");
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove("hidden");
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add("hidden");
    }
}
</script>';
    }
    
    return sprintf(
        '<div>
            <label for="%s" class="block text-sm font-medium text-gray-700 mb-2">
                %s%s
            </label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="flex text-sm text-gray-600">
                        <label for="%s" class="relative cursor-pointer rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                            <span>Subir archivo</span>
                            <input %s class="sr-only">
                        </label>
                        <p class="pl-1">o arrastra y suelta</p>
                    </div>
                    <p class="text-xs text-gray-500">%s</p>
                </div>
            </div>
            %s
            %s
            %s
            %s
        </div>',
        $id,
        htmlspecialchars($label),
        $requiredMark,
        $id,
        $attributes,
        $config['accept'] ? strtoupper(str_replace('.', '', $config['accept'])) . ' hasta 10MB' : 'Cualquier archivo hasta 10MB',
        $previewHtml,
        $errorHtml,
        $helpHtml,
        $script
    );
}
