<?php

//======================================================================
// PHP QR Code Generator Class
// Source: https://github.com/r-a-y/php-qrcode-generator-class
// NOTE: This class requires the PHP cURL extension to be enabled.
//======================================================================

class QRcode
{
    private $data;
    private $level;

    /**
     * QRcode constructor.
     * @param string $text
     * @param string $level
     */
    public function __construct($text = 'https://www.google.com', $level = 'L')
    {
        $this->data = empty($text) ? 'https://www.google.com' : $text;
        $this->level = $level;
    }

    /**
     * Get QR Code image data.
     * @param int $size
     * @param int $margin
     * @return string|false
     */
    private function get($size = 400, $margin = 4)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://chart.googleapis.com/chart');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'chs=' . $size . 'x' . $size . '&cht=qr&chld=' . $this->level . '|' . $margin . '&chl=' . urlencode($this->data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * Save QR Code to a file.
     * @param string $filename
     * @param int $size
     * @param int $margin
     * @return bool
     */
    public function save($filename, $size = 400, $margin = 4)
    {
        $response = $this->get($size, $margin);
        if ($response === false) {
            return false;
        }
        $fp = fopen($filename, 'w');
        fwrite($fp, $response);
        fclose($fp);
        return true;
    }
}


//======================================================================
// Main Application Logic
//======================================================================

class CommandTool
{
    private $theme_color_code = "\033[0;32m"; // Default Green
    const COLOR_GREEN = "\033[0;32m";
    const COLOR_RED = "\033[0;31m";
    const COLOR_YELLOW = "\033[1;33m";
    const COLOR_RESET = "\033[0m";

    /**
     * Runs the main application loop.
     */
    public function run()
    {
        // Check for cURL extension at the start
        if (!function_exists('curl_init')) {
            echo $this->colorize("Peringatan: Ekstensi cURL PHP tidak aktif. Fitur 'generate_qr' tidak akan berfungsi.\n", self::COLOR_YELLOW);
        }
        
        echo $this->colorize("=======================================\n", self::COLOR_YELLOW);
        echo $this->colorize("   Selamat Datang di PHP Tools v2.0    \n", self::COLOR_YELLOW);
        echo $this->colorize("=======================================\n\n", self::COLOR_YELLOW);
        echo "Ketik '" . self::COLOR_GREEN . "/menu" . self::COLOR_RESET . "' untuk melihat daftar tools.\n";
        echo "Ketik '" . self::COLOR_GREEN . "/exit" . self::COLOR_RESET . "' untuk keluar.\n\n";

        while (true) {
            $prompt = $this->colorize("tools@php", $this->theme_color_code) . self::COLOR_RESET . "> ";
            $input = readline($prompt);
            
            // Add command to history if not empty
            if ($input) {
                readline_add_history($input);
            }

            $this->handleCommand(trim($input));
        }
    }

    /**
     * Handles the user input command.
     * @param string $command The command entered by the user.
     */
    private function handleCommand($command)
    {
        $parts = explode(' ', $command, 2);
        $main_command = strtolower($parts[0] ?? '');

        switch ($main_command) {
            case '/menu':
                $this->showMenu();
                break;
            case '/tqto':
                $this->showThanks();
                break;
            case 'generate_qr':
                $this->generateQr();
                break;
            case 'bruteforce':
                $this->bruteforceTool();
                break;
            case 'calc':
                $expression = $parts[1] ?? '';
                $this->calculatorTool($expression);
                break;
            case 'theme':
                $theme_choice = $parts[1] ?? '';
                $this->setTheme($theme_choice);
                break;
            case '/exit':
                echo $this->colorize("Terima kasih telah menggunakan tools ini. Sampai jumpa!\n", self::COLOR_YELLOW);
                exit;
            case '':
                // Do nothing if input is empty
                break;
            default:
                echo $this->colorize("Perintah tidak dikenal: '{$command}'. Ketik /menu untuk bantuan.\n", self::COLOR_RED);
                break;
        }
    }

    /**
     * Displays the main menu.
     */
    private function showMenu()
    {
        $menu = <<<EOT

        Daftar Perintah yang Tersedia:
        --------------------------------
        /menu                   : Menampilkan menu ini.
        generate_qr             : Membuat QR Code dari teks atau URL.
        bruteforce              : Menjalankan tools bruteforce (simulasi).
        calc [ekspresi]         : Kalkulator sederhana. Contoh: calc 10 * 5
        theme [ijo|merah]       : Mengubah warna tema terminal.
        /tqto                   : Menampilkan credit dan ucapan terima kasih.
        /exit                   : Keluar dari program.
        
        EOT;
        echo $this->colorize($menu, $this->theme_color_code);
    }

    /**
     * Displays the thanks/credits.
     */
    private function showThanks()
    {
        $thanks = <<<EOT

        ==================================
        Developed by: Lana
        Thanks to: God, My Parents
        ==================================
        
        EOT;
        echo $this->colorize($thanks, $this->theme_color_code);
    }

    /**
     * Handles the QR code generation process.
     */
    private function generateQr()
    {
        if (!function_exists('curl_init')) {
            echo $this->colorize("Error: cURL tidak tersedia. Fitur ini tidak dapat dijalankan.\n", self::COLOR_RED);
            return;
        }
        
        echo $this->colorize("--- Generate QR Code ---\n", self::COLOR_YELLOW);
        $data = readline("Masukkan teks atau URL: ");
        if (empty($data)) {
            echo $this->colorize("Data tidak boleh kosong. Dibatalkan.\n", self::COLOR_RED);
            return;
        }

        $filename = readline("Masukkan nama file output (e.g., my_qr.png): ");
        if (empty($filename)) {
            $filename = 'qrcode_' . time() . '.png';
            echo $this->colorize("Nama file kosong, menggunakan default: {$filename}\n", self::COLOR_YELLOW);
        }

        try {
            $qr = new QRcode($data);
            if ($qr->save($filename)) {
                echo $this->colorize("\nSukses! QR Code disimpan sebagai '{$filename}'\n", self::COLOR_GREEN);
            } else {
                 echo $this->colorize("\nError: Gagal membuat QR Code. Periksa koneksi internet Anda.\n", self::COLOR_RED);
            }
        } catch (Exception $e) {
            echo $this->colorize("\nError: Terjadi kesalahan. " . $e->getMessage() . "\n", self::COLOR_RED);
        }
    }
    
    /**
     * New calculator tool.
     * Safely evaluates a simple mathematical expression without using eval().
     * @param string $expression The mathematical expression (e.g., "5 * 10").
     */
    private function calculatorTool($expression)
    {
        if (empty($expression)) {
            echo $this->colorize("Gunakan format: calc [angka] [operator] [angka]\nContoh: calc 100 + 35.5\n", self::COLOR_YELLOW);
            return;
        }

        // Ganti 'x' menjadi '*' untuk perkalian
        $expression = str_ireplace('x', '*', $expression);

        // Regex untuk memvalidasi dan mengekstrak bagian ekspresi
        // Mendukung angka positif/negatif dan desimal
        $pattern = '/^(\-?\d+\.?\d*)\s*([+\-\*\/])\s*(\-?\d+\.?\d*)$/';

        if (preg_match($pattern, $expression, $matches)) {
            $num1 = (float)$matches[1];
            $operator = $matches[2];
            $num2 = (float)$matches[3];
            $result = 0;

            switch ($operator) {
                case '+':
                    $result = $num1 + $num2;
                    break;
                case '-':
                    $result = $num1 - $num2;
                    break;
                case '*':
                    $result = $num1 * $num2;
                    break;
                case '/':
                    if ($num2 == 0) {
                        echo $this->colorize("Error: Tidak bisa membagi dengan nol.\n", self::COLOR_RED);
                        return;
                    }
                    $result = $num1 / $num2;
                    break;
            }
            echo $this->colorize("{$num1} {$operator} {$num2} = {$result}\n", self::COLOR_GREEN);

        } else {
            echo $this->colorize("Error: Format kalkulator tidak valid.\nContoh yang benar: '5 * 10' atau '50 / 2'.\n", self::COLOR_RED);
        }
    }

    /**
     * Placeholder for the bruteforce tool.
     */
    private function bruteforceTool()
    {
        echo $this->colorize("Ini adalah placeholder untuk tools bruteforce.\n", $this->theme_color_code);
        echo $this->colorize("Fitur ini sedang dalam pengembangan.\n\n", $this->theme_color_code);
    }

    /**
     * Sets the terminal theme color.
     * @param string $color The chosen color ('ijo' or 'merah').
     */
    private function setTheme($color)
    {
        $color = strtolower($color);
        if ($color == 'ijo') {
            $this->theme_color_code = self::COLOR_GREEN;
            echo $this->colorize("Tema berhasil diubah menjadi hijau.\n", $this->theme_color_code);
        } elseif ($color == 'merah') {
            $this->theme_color_code = self::COLOR_RED;
            echo $this->colorize("Tema berhasil diubah menjadi merah.\n", $this->theme_color_code);
        } else {
            echo $this->colorize("Pilihan tema tidak valid. Gunakan 'ijo' atau 'merah'.\n", self::COLOR_RED);
        }
    }

    /**
     * A helper function to wrap text in ANSI color codes.
     * @param string $text The text to colorize.
     * @param string $color_code The ANSI color code.
     * @return string The colorized text.
     */
    private function colorize($text, $color_code)
    {
        return $color_code . $text . self::COLOR_RESET;
    }
}

// Instantiate and run the application
$tool = new CommandTool();
$tool->run();

