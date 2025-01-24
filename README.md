# OpenAi PHP Class

The `OpenAi` class is a PHP wrapper designed to interact with OpenAI's API, specifically for chat completions and image generation using DALL-E. Below is a guide on how to use this class effectively.

## Installation

Ensure you have PHP installed with cURL support. You can include the `OpenAi` class in your project by requiring the file containing the class.

```php
require_once 'path/to/OpenAi.php';
```

## Initialization

Create an instance of the `OpenAi` class.

```php
$openai = new OpenAi();
```

## Setting the API Key

You need to set your OpenAI API key to authenticate requests.

```php
$openai->key('your-api-key-here');
```

## Using the Chat

### Setting the Model

You can specify the model to use for chat completions. If not set, it defaults to `gpt-4`.

```php
$openai->model('gpt-4');
```

### Setting the Role

You can set the role for the chat. Available roles are `user`, `assistant`, and `system`. The default role is `user`.

```php
$openai->role('user');
```

### Sending a Prompt

To send a text prompt and receive a response:

```php
$response = $openai->chat("Hello, how are you?");
echo $response;
```

### Streaming Responses

For streaming responses, you can pass a callback function or set the stream parameter to `true`.

#### Using a Callback Function

```php
$openai->chat("Tell me a story.", function($token, $done) {
    if ($done) {
        echo "\nStream complete.\n";
    } else {
        echo $token;
    }
});
```

#### Printing Stream Directly

```php
$openai->chat("Explain the theory of relativity.", true);
```

## Generating Images with DALL-E

### Setting the Model

You can specify the model to use for image generation. If not set, it defaults to `dall-e-3`.

```php
$openai->model('dall-e-3'); // or 'dall-e-2'
```

### Generating Images

To generate images based on a prompt:

```php
$images = $openai->image("A futuristic cityscape at sunset.");
print_r($images);
```

You can also specify the size, number of images, and quality:

```php
$images = $openai->image("A futuristic cityscape at sunset.", '1024x1024', 1, 'hd');
print_r($images);
```

## Size and Quality Options

Square, standard quality images are the fastest to generate. The default size of generated images is `1024x1024` pixels, but each model has different options:

### DALL·E 2
- **Size Options**: `256x256`, `512x512`, `1024x1024`
- **Quality Options**: Only `standard`
- **Requests**: Up to 10 images at a time using the `n` parameter.

Example:
```php
$images = $openai->image("A futuristic cityscape at sunset.", '512x512', 3, 'standard');
print_r($images);
```

### DALL·E 3
- **Size Options**: `1024x1024`, `1024x1792`, `1792x1024`
- **Quality Options**: Defaults to `standard`. Use `hd` for enhanced detail.
- **Requests**: Only 1 image at a time, but you can make parallel requests for more.

Example:
```php
$images = $openai->image("A futuristic cityscape at sunset.", '1024x1792', 1, 'hd');
print_r($images);
```

## Error Handling

The class throws exceptions for errors such as missing API keys, invalid roles, and API connection issues. Ensure to handle these exceptions in your code.

```php
try {
    $response = $openai->chat("Hello, how are you?");
    echo $response;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

## Example Usage

Here is a complete example demonstrating the usage of the `OpenAi` class:

```php
require_once 'path/to/OpenAi.php';

$openai = new OpenAi();
$openai->key('your-api-key-here');

try {
    // Chat example
    $openai->model('gpt-4');
    $openai->role('user');
    $response = $openai->chat("What is the capital of France?");
    echo $response . "\n";

    // Image generation example
    $openai->model('dall-e-3');
    $images = $openai->image("A futuristic cityscape at sunset.", '1024x1792', 1, 'hd');
    print_r($images);

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

⭐ If you liked what I did, if it was useful to you, or if it served as a starting point for something more magical, let me know with a star 💚.
