<?php
/**
 * Select Component
 * 
 * Uso:
 * include __DIR__ . '/components/form/select.php';
 * echo renderSelect('estado', 'Estado', $options, $selected, ['required' => true]);
 * 
 * @param string $name - Nombre del select
 * @param string $label - Label del select
 * @param array $options - Array de opciones ['value' => 'label', ...]
 * @param string $selected - Valor seleccionado
 * @param array $config - Opciones adicionales
 * @return string HTML del select
 */

function renderSelect(string $name, string $label, array $options, string $selected = '', array $config = []): string
{
    $defaults = [
        'required' => false,
        'disabled' => false,
        'error' => '',
        'help' => '',
        'placeholder' => 'Seleccionar...',
        'class' => '',
        'multiple' => false,
    ];
    
    $settings = array_merge($defaults, $config);
    
    $id = 'select_' . $name;
    $selectClasses = 'block w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 sm:text-sm';
    
    if ($settings['error']) {
        $selectClasses .= ' border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500';
    } else {
        $selectClasses .= ' border-gray-300';
    }
    
    if ($settings['disabled']) {
        $selectClasses .= ' bg-gray-100 cursor-not-allowed';
    }
    
    if ($settings['class']) {
        $selectClasses .= ' ' . $settings['class'];
    }
    
    $attributes = sprintf('name="%s" id="%s"', htmlspecialchars($name), $id);
    
    if ($settings['required']) $attributes .= ' required';
    if ($settings['disabled']) $attributes .= ' disabled';
    if ($settings['multiple']) $attributes .= ' multiple';
    
    $requiredMark = $settings['required'] ? '<span class="text-red-500 ml-1">*</span>' : '';
    
    $optionsHtml = '';
    if ($settings['placeholder'] && !$settings['multiple']) {
        $optionsHtml .= sprintf(
            '<option value="" %s>%s</option>',
            $selected === '' ? 'selected' : '',
            htmlspecialchars($settings['placeholder'])
        );
    }
    
    foreach ($options as $value => $optionLabel) {
        $isSelected = '';
        if ($settings['multiple']) {
            $isSelected = is_array($selected) && in_array($value, $selected) ? 'selected' : '';
        } else {
            $isSelected = (string)$value === (string)$selected ? 'selected' : '';
        }
        
        $optionsHtml .= sprintf(
            '<option value="%s" %s>%s</option>',
            htmlspecialchars($value),
            $isSelected,
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
            <label for="%s" class="block text-sm font-medium text-gray-700 mb-2">
                %s%s
            </label>
            <select %s class="%s">%s</select>
            %s
            %s
        </div>',
        $id,
        htmlspecialchars($label),
        $requiredMark,
        $attributes,
        $selectClasses,
        $optionsHtml,
        $errorHtml,
        $helpHtml
    );
}

function renderSelectWithIcon(string $name, string $label, array $options, string $selected = '', string $icon = '', array $config = []): string
{
    $defaults = [
        'required' => false,
        'disabled' => false,
        'error' => '',
        'help' => '',
        'placeholder' => 'Seleccionar...',
    ];
    
    $settings = array_merge($defaults, $config);
    
    $id = 'select_' . $name;
    $selectClasses = 'block w-full pl-10 pr-10 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 sm:text-sm appearance-none';
    
    if ($settings['error']) {
        $selectClasses .= ' border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500';
    } else {
        $selectClasses .= ' border-gray-300';
    }
    
    if ($settings['disabled']) {
        $selectClasses .= ' bg-gray-100 cursor-not-allowed';
    }
    
    $attributes = sprintf('name="%s" id="%s"', htmlspecialchars($name), $id);
    
    if ($settings['required']) $attributes .= ' required';
    if ($settings['disabled']) $attributes .= ' disabled';
    
    $requiredMark = $settings['required'] ? '<span class="text-red-500 ml-1">*</span>' : '';
    
    $optionsHtml = '';
    if ($settings['placeholder']) {
        $optionsHtml .= sprintf(
            '<option value="" %s>%s</option>',
            $selected === '' ? 'selected' : '',
            htmlspecialchars($settings['placeholder'])
        );
    }
    
    foreach ($options as $value => $optionLabel) {
        $isSelected = (string)$value === (string)$selected ? 'selected' : '';
        $optionsHtml .= sprintf(
            '<option value="%s" %s>%s</option>',
            htmlspecialchars($value),
            $isSelected,
            htmlspecialchars($optionLabel)
        );
    }
    
    $iconHtml = sprintf(
        '<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">%s</div>',
        $icon
    );
    
    $arrowHtml = '<div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>';
    
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
            <label for="%s" class="block text-sm font-medium text-gray-700 mb-2">
                %s%s
            </label>
            <div class="relative">
                %s
                <select %s class="%s">%s</select>
                %s
            </div>
            %s
            %s
        </div>',
        $id,
        htmlspecialchars($label),
        $requiredMark,
        $iconHtml,
        $attributes,
        $selectClasses,
        $optionsHtml,
        $arrowHtml,
        $errorHtml,
        $helpHtml
    );
}

function renderSelectGroup(string $name, string $label, array $groups, string $selected = '', array $config = []): string
{
    $defaults = [
        'required' => false,
        'disabled' => false,
        'error' => '',
        'help' => '',
        'placeholder' => 'Seleccionar...',
    ];
    
    $settings = array_merge($defaults, $config);
    
    $id = 'select_' . $name;
    $selectClasses = 'block w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 sm:text-sm';
    
    if ($settings['error']) {
        $selectClasses .= ' border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500';
    } else {
        $selectClasses .= ' border-gray-300';
    }
    
    if ($settings['disabled']) {
        $selectClasses .= ' bg-gray-100 cursor-not-allowed';
    }
    
    $attributes = sprintf('name="%s" id="%s"', htmlspecialchars($name), $id);
    
    if ($settings['required']) $attributes .= ' required';
    if ($settings['disabled']) $attributes .= ' disabled';
    
    $requiredMark = $settings['required'] ? '<span class="text-red-500 ml-1">*</span>' : '';
    
    $optionsHtml = '';
    if ($settings['placeholder']) {
        $optionsHtml .= sprintf(
            '<option value="" %s>%s</option>',
            $selected === '' ? 'selected' : '',
            htmlspecialchars($settings['placeholder'])
        );
    }
    
    foreach ($groups as $groupLabel => $groupOptions) {
        $optionsHtml .= sprintf('<optgroup label="%s">', htmlspecialchars($groupLabel));
        
        foreach ($groupOptions as $value => $optionLabel) {
            $isSelected = (string)$value === (string)$selected ? 'selected' : '';
            $optionsHtml .= sprintf(
                '<option value="%s" %s>%s</option>',
                htmlspecialchars($value),
                $isSelected,
                htmlspecialchars($optionLabel)
            );
        }
        
        $optionsHtml .= '</optgroup>';
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
            <label for="%s" class="block text-sm font-medium text-gray-700 mb-2">
                %s%s
            </label>
            <select %s class="%s">%s</select>
            %s
            %s
        </div>',
        $id,
        htmlspecialchars($label),
        $requiredMark,
        $attributes,
        $selectClasses,
        $optionsHtml,
        $errorHtml,
        $helpHtml
    );
}

function renderRadioGroup(string $name, string $label, array $options, string $selected = '', array $config = []): string
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
    
    $containerClass = $settings['inline'] ? 'flex items-center space-x-4' : 'space-y-2';
    
    $optionsHtml = '';
    foreach ($options as $value => $optionLabel) {
        $id = 'radio_' . $name . '_' . $value;
        $isChecked = (string)$value === (string)$selected ? 'checked' : '';
        
        $attributes = sprintf(
            'type="radio" name="%s" id="%s" value="%s"',
            htmlspecialchars($name),
            $id,
            htmlspecialchars($value)
        );
        
        if ($settings['required']) $attributes .= ' required';
        if ($settings['disabled']) $attributes .= ' disabled';
        if ($isChecked) $attributes .= ' checked';
        
        $optionsHtml .= sprintf(
            '<div class="flex items-center">
                <input %s class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300">
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
