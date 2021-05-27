<?php
    class Algorithm{
        public function aesCrypt($data){
            //Define cipher
            $cipher = "aes-256-cbc";

            //Generate a 256-bit encryption key
            $encryption_key = openssl_random_pseudo_bytes(32);
            $_SESSION["sk"] = $encryption_key;
            // Generate an initialization vector
            $iv_size = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($iv_size);
            $_SESSION["ivs"] = $iv_size;
            $_SESSION["ivk"] = $iv;
            return $encrypted_data = openssl_encrypt($data, $cipher, $encryption_key, 0, $iv);
        }
        public function stegano($file, $message){
            // Encode the message into a binary string.
            $binaryMessage = '';
            for ($i = 0; $i < mb_strlen($message); ++$i)
            {
                $character = ord($message[$i]);
                $binaryMessage .= str_pad(decbin($character), 8, '0', STR_PAD_LEFT);
            }
            // Inject the 'end of text' character into the string.
            $binaryMessage .= '00000011';

            // Load the image into memory.
            $img = imagecreatefromjpeg($file);

            // Get image dimensions.
            $width = imagesx($img);
            $height = imagesy($img);

            $messagePosition = 0;

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {

                    if (!isset($binaryMessage[$messagePosition])) {
                        // No need to keep processing beyond the end of the message.
                        break 2;
                    }

                    // Extract the colour.
                    $rgb = imagecolorat($img, $x, $y);
                    $colors = imagecolorsforindex($img, $rgb);

                    $red = $colors['red'];
                    $green = $colors['green'];
                    $blue = $colors['blue'];
                    $alpha = $colors['alpha'];

                    // Convert the blue to binary.
                    $binaryBlue = str_pad(decbin($blue), 8, '0', STR_PAD_LEFT);

                    // Replace the final bit of the blue colour with our message.
                    $binaryBlue[strlen($binaryBlue) - 1] = $binaryMessage[$messagePosition];
                    $newBlue = bindec($binaryBlue);

                    // Inject that new colour back into the image.
                    $newColor = imagecolorallocatealpha($img, $red, $green, $newBlue, $alpha);
                    imagesetpixel($img, $x, $y, $newColor);

                    // Advance message position.
                    $messagePosition++;
                }
            }

            // Save the image to a file.
            $newImage = time().'_'.'secret.png';
            imagepng($img, $newImage, 9);

            // Destroy the image handler.
            imagedestroy($img);
        }
        public function steganoDecrypt($file){
            // Read the file into memory.
            $img = imagecreatefrompng($file);

            // Read the message dimensions.
            $width = imagesx($img);
            $height = imagesy($img);

            // Set the message.
            $binaryMessage = '';

            // Initialise message buffer.
            $binaryMessageCharacterParts = [];

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {

                    // Extract the colour.
                    $rgb = imagecolorat($img, $x, $y);
                    $colors = imagecolorsforindex($img, $rgb);

                    $blue = $colors['blue'];

                    // Convert the blue to binary.
                    $binaryBlue = decbin($blue);

                    // Extract the least significant bit into out message buffer..
                    $binaryMessageCharacterParts[] = $binaryBlue[strlen($binaryBlue) - 1];

                    if (count($binaryMessageCharacterParts) == 8) {
                        // If we have 8 parts to the message buffer we can update the message string.
                        $binaryCharacter = implode('', $binaryMessageCharacterParts);
                        $binaryMessageCharacterParts = [];
                        if ($binaryCharacter == '00000011') {
                            // If the 'end of text' character is found then stop looking for the message.
                            break 2;
                        }
                        else {
                            // Append the character we found into the message.
                            $binaryMessage .= $binaryCharacter;
                        }
                    }
                }
            }

            // Convert the binary message we have found into text.
            $message = '';
            for ($i = 0; $i < strlen($binaryMessage); $i += 8) {
                $character = mb_substr($binaryMessage, $i, 8);
                $message .= chr(bindec($character));
            }

            return $message;
        }
        public function aesDecrypt($encrypted_data,$encryption_key,$iv){
            $cipher = "aes-256-cbc";
            $decrypted_data = openssl_decrypt($encrypted_data, $cipher, $encryption_key, 0, $iv);
            return $decrypted_data;
        }
    }
?>
