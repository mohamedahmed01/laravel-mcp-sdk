<?php

require __DIR__ . '/../vendor/autoload.php';

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

// Configuration
$srcDir = realpath(__DIR__ . '/../src');
$docsDir = realpath(__DIR__ . '/../docs') . '/api';

// Create docs directory if it doesn't exist
if (!is_dir($docsDir)) {
    mkdir($docsDir, 0777, true);
}

// Function to parse PHPDoc comments
function parseDocComment($docComment) {
    if (empty($docComment)) {
        return '';
    }
    $docComment = trim(preg_replace('/^\s*\/\*+|\s*\*+\/\s*$/s', '', $docComment));
    $docComment = preg_replace('/^\s*\* ?/m', '', $docComment);
    return $docComment;
}

// Function to generate documentation for a file
function generateFileDoc($file, $docsDir) {
    $content = file_get_contents($file);
    $tokens = token_get_all($content);
    $namespace = '';
    $classes = [];
    $currentClass = null;

    foreach ($tokens as $token) {
        if (!is_array($token)) {
            continue;
        }

        switch ($token[0]) {
            case T_NAMESPACE:
                $namespace = '';
                $i = array_search($token, $tokens) + 2;
                while (isset($tokens[$i]) && is_array($tokens[$i])) {
                    if ($tokens[$i][0] === T_STRING || $tokens[$i][0] === T_NS_SEPARATOR) {
                        $namespace .= $tokens[$i][1];
                    }
                    $i++;
                }
                break;

            case T_CLASS:
                $i = array_search($token, $tokens) + 2;
                if (isset($tokens[$i]) && is_array($tokens[$i])) {
                    $className = trim($tokens[$i][1]);
                    if (empty($className)) {
                        continue 2;
                    }
                    
                    $currentClass = [
                        'name' => $className,
                        'namespace' => $namespace,
                        'docComment' => '',
                        'methods' => []
                    ];
                    
                    // Look for class doc comment
                    $j = array_search($token, $tokens) - 2;
                    while (isset($tokens[$j]) && is_array($tokens[$j])) {
                        if ($tokens[$j][0] === T_DOC_COMMENT) {
                            $currentClass['docComment'] = parseDocComment($tokens[$j][1]);
                            break;
                        }
                        $j--;
                    }
                    
                    $classes[] = $currentClass;
                }
                break;

            case T_FUNCTION:
                if ($currentClass !== null) {
                    $i = array_search($token, $tokens) + 2;
                    if (isset($tokens[$i]) && is_array($tokens[$i])) {
                        $methodName = trim($tokens[$i][1]);
                        if (empty($methodName)) {
                            continue 2;
                        }
                        
                        $method = ['name' => $methodName, 'docComment' => ''];
                        
                        // Look for method doc comment
                        $j = array_search($token, $tokens) - 2;
                        while (isset($tokens[$j]) && is_array($tokens[$j])) {
                            if ($tokens[$j][0] === T_DOC_COMMENT) {
                                $method['docComment'] = parseDocComment($tokens[$j][1]);
                                break;
                            }
                            $j--;
                        }
                        
                        $classes[count($classes) - 1]['methods'][] = $method;
                    }
                }
                break;
        }
    }

    // Generate documentation
    foreach ($classes as $class) {
        if (empty(trim($class['name']))) {
            continue;
        }
        
        $classDoc = "# {$class['name']}\n\n";
        $classDoc .= "Namespace: `{$class['namespace']}`\n\n";
        
        if (!empty($class['docComment'])) {
            $classDoc .= "{$class['docComment']}\n\n";
        }

        if (!empty($class['methods'])) {
            $classDoc .= "## Methods\n\n";
            foreach ($class['methods'] as $method) {
                if (empty(trim($method['name']))) {
                    continue;
                }
                $classDoc .= "### {$method['name']}\n\n";
                if (!empty($method['docComment'])) {
                    $classDoc .= "{$method['docComment']}\n\n";
                }
            }
        }

        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $class['namespace'] . '_' . $class['name']);
        $filename = $docsDir . '/' . $safeFilename . '.md';
        file_put_contents($filename, $classDoc);
    }

    return $classes;
}

// Generate index file
$indexContent = "# API Documentation\n\n";
$indexContent .= "Welcome to the Laravel MCP SDK API documentation.\n\n";
$indexContent .= "## Classes\n\n";

// Process all PHP files in src directory
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir));
$allClasses = [];
$seenClasses = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $classes = generateFileDoc($file, $docsDir);
        foreach ($classes as $class) {
            if (empty(trim($class['name']))) {
                continue;
            }
            
            // Skip duplicate classes
            $classKey = $class['namespace'] . '\\' . $class['name'];
            if (isset($seenClasses[$classKey])) {
                continue;
            }
            $seenClasses[$classKey] = true;
            
            $allClasses[] = [
                'name' => $class['name'],
                'namespace' => $class['namespace'],
                'file' => preg_replace('/[^a-zA-Z0-9_-]/', '_', $class['namespace'] . '_' . $class['name']) . '.md'
            ];
        }
    }
}

// Sort classes by namespace and name
usort($allClasses, function($a, $b) {
    $nsCompare = strcmp($a['namespace'], $b['namespace']);
    return $nsCompare !== 0 ? $nsCompare : strcmp($a['name'], $b['name']);
});

// Group classes by namespace
$groupedClasses = [];
foreach ($allClasses as $class) {
    if (empty(trim($class['name']))) {
        continue;
    }
    $namespace = trim($class['namespace']) ?: 'Global';
    if (!isset($groupedClasses[$namespace])) {
        $groupedClasses[$namespace] = [];
    }
    $groupedClasses[$namespace][] = $class;
}

// Generate index content
foreach ($groupedClasses as $namespace => $classes) {
    $indexContent .= "\n### {$namespace}\n\n";
    foreach ($classes as $class) {
        if (!empty(trim($class['name']))) {
            $indexContent .= "* [{$class['name']}](api/{$class['file']})\n";
        }
    }
}

file_put_contents(dirname($docsDir) . '/index.md', $indexContent);

echo "Documentation generated successfully in {$docsDir}\n"; 