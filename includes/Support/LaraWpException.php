<?php

namespace LaraWp\Includes\Support;

use Exception;

class LaraWpException extends Exception {

    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    // Override the __toString method to output custom HTML
    public function __toString() {
        return $this->renderHtml();
    }

    // Render the exception as HTML with Laravel-like design
    protected function renderHtml() {
        $html = '
            <html>
                <head>
                    <style>
                        body {
                            background-color: #f5f5f5;
                            color: #333;
                            font-family: "Nunito", sans-serif;
                            padding: 20px;
                            margin: 0;
                        }
                        .exception-container {
                            background-color: white;
                            border-radius: 5px;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                            max-width: 900px;
                            margin: 0 auto;
                            padding: 20px;
                            font-size: 16px;
                        }
                        .exception-header {
                            border-bottom: 1px solid #e8e8e8;
                            padding-bottom: 10px;
                            margin-bottom: 20px;
                        }
                        .exception-header h1 {
                            font-size: 24px;
                            color: #f44336;
                        }
                        .exception-message {
                            font-weight: bold;
                            font-size: 18px;
                        }
                        .exception-details {
                            margin-top: 20px;
                        }
                        .exception-details .file,
                        .exception-details .line {
                            font-family: "Courier New", Courier, monospace;
                            color: #888;
                        }
                        .trace-container {
                            margin-top: 20px;
                            background-color: #fafafa;
                            padding: 10px;
                            border-radius: 5px;
                            border: 1px solid #ddd;
                            overflow-y: auto;
                            max-height: 400px;
                        }
                        .trace-item {
                            padding: 10px;
                            margin-bottom: 10px;
                            border-bottom: 1px solid #f0f0f0;
                        }
                        .trace-item:last-child {
                            border-bottom: none;
                        }
                        .trace-item .file {
                            font-family: "Courier New", Courier, monospace;
                            color: #795da3;
                        }
                        .trace-item .function {
                            font-family: "Courier New", Courier, monospace;
                            color: #0086b3;
                        }
                        .code-preview {
                            background-color: #282c34;
                            color: #abb2bf;
                            padding: 15px;
                            border-radius: 4px;
                            overflow-x: auto;
                            max-width: 100%;
                            margin-top: 15px;
                            font-size: 14px;
                            line-height: 1.5;
                            white-space: pre;
                        }
                        .code-preview .line-number {
                            display: inline-block;
                            width: 50px;
                            color: #666;
                        }
                    </style>
                </head>
                <body>
                    <div class="exception-container">
                        <div class="exception-header">
                            <h1>Whoops! Something went wrong.</h1>
                            <p class="exception-message">' . htmlspecialchars($this->getMessage()) . '</p>
                        </div>
                        <div class="exception-details">
                            <p><strong>File:</strong> <span class="file">' . htmlspecialchars($this->getFile()) . '</span></p>
                            <p><strong>Line:</strong> <span class="line">' . $this->getLine() . '</span></p>
                        </div>
                        ' . $this->getFormattedTrace() . '
                        ' . $this->getFilePreview() . '
                    </div>
                </body>
            </html>';
        wp_die($html);
    }

    // Format and color-code the stack trace
    protected function getFormattedTrace() {
        $trace = $this->getTrace();
        $html = '<div class="trace-container"><h3>Stack Trace:</h3>';

        foreach ($trace as $index => $frame) {
            $file = isset($frame['file']) ? $frame['file'] : '[internal function]';
            $line = isset($frame['line']) ? $frame['line'] : 'N/A';
            $function = isset($frame['function']) ? $frame['function'] : 'N/A';
            $class = isset($frame['class']) ? $frame['class'] : '';
            $type = isset($frame['type']) ? $frame['type'] : '';

            $html .= '
                <div class="trace-item">
                    <span class="file">' . htmlspecialchars($file) . '</span> (Line: <span class="line">' . $line . '</span>)
                    <br>
                    <span class="function">' . htmlspecialchars($class . $type . $function) . '()</span>
                </div>';
        }

        $html .= '</div>';
        return $html;
    }

    // File preview for context
    protected function getFilePreview($contextLines = 5) {
        if (!is_readable($this->getFile())) {
            return '';
        }

        $fileContent = file($this->getFile());
        $startLine = max(0, $this->getLine() - $contextLines - 1);
        $endLine = min(count($fileContent) - 1, $this->getLine() + $contextLines - 1);

        $html = '<div class="code-preview">';
        for ($i = $startLine; $i <= $endLine; $i++) {
            $lineNumber = $i + 1;
            $lineContent = htmlspecialchars($fileContent[$i]);
            $highlight = $lineNumber == $this->getLine() ? ' style="background-color: #3d3d3d;"' : '';
            $html .= '<span class="line-number">' . $lineNumber . '</span><span' . $highlight . '>' . $lineContent . '</span><br>';
        }
        $html .= '</div>';

        return $html;
    }
}
