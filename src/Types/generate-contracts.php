<?php

echo "Iniating types generation.\n";
function getSimpleClassName($phpType) {
    $path = explode('\\', $phpType);
    return array_pop($path);
}

function mapPhpTypeToTypeScript($phpType, $isClass = false) {
    if ($isClass) {
        return $phpType;
    }

    $typeMappings = [
        'int' => 'number',
        'float' => 'number',
        'string' => 'string',
        'bool' => 'boolean',
        'array' => 'any[]',
    ];

    return $typeMappings[$phpType] ?? 'any';
}

$directory = __DIR__;
$files = scandir($directory);
$schemas = [];

foreach ($files as $file) {
    if (is_file($directory . '/' . $file) && $file !== 'generate-contract.php' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        echo "Processing file: $file\n";
        require_once $file;

        $classNameWithPath = pathinfo($file, PATHINFO_FILENAME);
        $className = "Olmec\\OlmecNotepress\\Types\\" . $classNameWithPath;
        
        if (!class_exists($className)) {
            continue;
        }
        
        $reflectionClass = new \ReflectionClass($className);
        $properties = $reflectionClass->getProperties();
        $schema = [];

        foreach ($properties as $property) {
            $name = $property->getName();
            $type = $property->getType();
            $phpType = $type ? $type->getName() : 'mixed';
            $isClass = $type && !$type->isBuiltin();
        
            if ($isClass) {
                $tsType = getSimpleClassName($phpType);
            } else {
                $tsType = mapPhpTypeToTypeScript($phpType);
            }
        
            $schema[$name] = $tsType;
        }

        $schemas[$classNameWithPath] = $schema;
    }
}

file_put_contents($directory . '/Schemas.json', json_encode($schemas, JSON_PRETTY_PRINT));
echo "JSON schema generated.\n";