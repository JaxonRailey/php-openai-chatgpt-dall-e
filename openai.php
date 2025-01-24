<?php

    class OpenAi {

        protected string $key   = '';
        protected mixed  $model = null;
        protected string $role  = 'user';


        /**
         * Set or get API Key
         *
         * @param string $key (optional)
         *
         * @return mixed
         */

        public function key(string $key = null) {

            if (!$key) {
                return $this->key;
            }

            $this->key = $key;

            return $this;
        }


        /**
         * Set or get model
         *
         * @param string $model (optional)
         *
         * @return mixed
         */

        public function model(string $model = null) {

            if (!$model) {
                return $this->model;
            }

            $this->model = $model;

            return $this;
        }


        /**
         * Set or get role
         *
         * @param string $role (user, assistant, system, default 'user')
         *
         * @return mixed
         */

        public function role(string $role = null) {

            if (!$role) {
                return $this->role;
            }

            if (!in_array($role, ['user', 'assistant', 'system'])) {
                throw new Exception(
                    "[Invalid Role] The specified role ('$role') is not supported. " .
                    "Please use one of the following roles: 'user', 'assistant', 'system'."
                );
            }

            $this->role = $role;

            return $this;
        }


        /**
         * Send a text prompt
         *
         * @param string $prompt
         * @param mixed $stream (optional)
         *
         * @return string
         */

        public function chat(string $prompt, $stream = null) {

            $data = [
                'model'     => $this->model ?? 'gpt-4',
                'messages'  => [
                    ['role' => $this->role, 'content' => $prompt]
                ]
            ];

            $endpoint = 'https://api.openai.com/v1/chat/completions';

            return $this->request($endpoint, $data, $stream);
        }


        /**
         * Generate images using DALL-E
         *
         * @param string $prompt
         * @param string $size (default: '1024x1024', optional)
         * @param int $n (default: 1, optional)
         *
         * @return array
         */

        public function image(string $prompt, string $size = '1024x1024', int $n = 1, string $quality = 'standard') {

            $data = [
                'model'   => $this->model ?? 'dall-e-3',
                'prompt'  => $prompt,
                'size'    => $size,
                'n'       => $n,
                'quality' => $quality
            ];

            $endpoint = 'https://api.openai.com/v1/images/generations';
            $response = $this->request($endpoint, $data);

            return array_column($response, 'url');
        }

        protected function request(string $endpoint, array $data, $stream = null) {

            if (empty($this->key)) {
                throw new Exception(
                    "[API Key Missing] The API key has not been set. " .
                    "Please use the key() method to set a valid API key."
                );
            }

            $streaming = is_callable($stream) || $stream === true;
            $print     = $stream === true;

            if ($streaming) {
                $data['stream'] = true;
            }

            $curl = curl_init($endpoint);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->key
            ];

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

            if ($streaming) {
                ob_end_flush();
                curl_setopt($curl, CURLOPT_WRITEFUNCTION, function($curl, $chunk) use ($stream, $print) {
                    foreach (explode("\n", $chunk) as $line) {
                        if (strpos($line, 'data: ') === 0) {
                            $jsonData = substr($line, 6);
                            if ($jsonData === '[DONE]') {
                                if (is_callable($stream)) {
                                    $stream(null, true);
                                }
                                return strlen($chunk);
                            }
                            $decodedData = json_decode($jsonData, true);
                            if ($decodedData && isset($decodedData['choices'][0]['delta']['content'])) {
                                $token = $decodedData['choices'][0]['delta']['content'];
                                if ($print) {
                                    echo $token;
                                    flush();
                                } elseif (is_callable($stream)) {
                                    $stream($token, false);
                                }
                            }
                        }
                    }
                    return strlen($chunk);
                });
            }

            $response = curl_exec($curl);

            if (!$response) {
                throw new Exception(
                    "[Connection Error] Unable to connect to the API. " .
                    "Details: " . curl_error($curl)
                );
            }

            curl_close($curl);

            if (!$streaming) {
                $response = json_decode($response, true);
                if (isset($response['error']['message'])) {
                    throw new Exception(
                        "[API Error] The API request returned an error. " .
                        "Details: " . $response['error']['message']
                    );
                }

                if (str_contains($endpoint, 'images/generations')) {
                    return $response['data'] ?? [];
                }

                if (str_contains($endpoint, 'chat/completions')) {
                    return $response['choices'][0]['message']['content'] ?? '';
                }
            }
        }
    }
