<?php
/*
Plugin Name: Hangman Game
Description: Simple hangman game for WordPress
Version: 0.2.2
Author: <a href="https://t.ly/duzp9">SzumisKarp</a>
*/

// Initialize the session when WordPress is initialized
add_action('init', 'hangman_game_start_session', 1);

function hangman_game_start_session() {
    if (!session_id()) {
        session_start();
    }
}

// Add a menu for the Hangman Game in the WordPress admin panel
add_action('admin_menu', 'hangman_game_menu');

function hangman_game_menu() {
    add_menu_page('Hangman Game', 'Hangman Game', 'manage_options', 'hangman-game', 'hangman_game_page');
}

// Function to display the Hangman Game page in the WordPress admin panel
function hangman_game_page() {
    // CSS styles for better presentation
    echo '<style>
    /* CSS styles for the Hangman Game */
    #hangman-game {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        background-color: #f8f8f8;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    #hangman-game p {
        margin: 10px 0;
    }

    #hangman-game form {
        margin-top: 20px;
    }

    #hangman-game label {
        font-weight: bold;
    }

    #hangman-game input[type="text"] {
        padding: 8px;
        margin-right: 10px;
    }

    #hangman-game input[type="submit"] {
        padding: 8px 16px;
        background-color: #4caf50;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    #hangman-game input[type="submit"]:hover {
        background-color: #45a049;
    }

    #hangman-game input[type="submit"][name="reset_game"] {
        background-color: #3498db;
    }

    #hangman-game input[type="submit"][name="reset_game"]:hover {
        background-color: #2980b9;
    }

    .hangman-correct {
        color: #4caf50;
        font-weight: bold;
    }

    .hangman-incorrect {
        color: #e74c3c;
        font-weight: bold;
    }

    .hangman-win {
        color: #2ecc71;
        font-weight: bold;
    }

    .hangman-lose {
        color: #e74c3c;
        font-weight: bold;
    }
    </style>';

    // Array of words for the Hangman game
    $hangman_words = array(
        'jabłko', 'banan', 'gruszka', 'pomarańcza', 'truskawka', 'malina',
        'marchew', 'ziemniak', 'papryka', 'kalafior', 'brokuł', 'seler',
        'nike', 'adidas', 'puma', 'reebok', 'converse', 'vans',
        'mercedes', 'bmw', 'audi', 'toyota', 'volkswagen', 'honda',
        'polska', 'niemcy', 'francja', 'włochy', 'hiszpania', 'rosja',
        'warszawa', 'kraków', 'wrocław', 'poznań', 'gdynia', 'sopot',
        'pomidor', 'cytryna', 'grejpfrut', 'ziemniak', 'batat', 'kalifornia',
        'limonka', 'mandarynka', 'cytrus', 'arbuz', 'koktajl', 'kaktus',
        'kanapka', 'lazania', 'spaghetti', 'hamburger', 'kebab', 'tortilla',
        'dżinsy', 'chucks', 'buty', 'kapcie', 'sandały', 'obcasy',
        'jezz', 'bluza', 'kamień', 'pop', 'hiphop', 'reggae',
        'szczecin', 'toruń', 'łódź', 'połczyn', 'kłodzko', 'żagań'
    );

    // Initialize or reset session variables for the game
    if (!isset($_SESSION['hangman_word']) || isset($_POST['reset_game'])) {
        $_SESSION['hangman_word'] = mb_strtolower($hangman_words[array_rand($hangman_words)], 'UTF-8');
        $_SESSION['hangman_correct_guesses'] = array();
        $_SESSION['hangman_incorrect_guesses'] = array();
        $_SESSION['hangman_attempts'] = 0;
    }

    // Process user's guess if submitted
    if (isset($_POST['guess'])) {
        $user_guess = mb_strtolower(sanitize_text_field($_POST['guess']), 'UTF-8');

        // Check if the guessed letter is correct or incorrect
        if (mb_strpos($_SESSION['hangman_word'], $user_guess, 0, 'UTF-8') !== false) {
            echo '<p class="hangman-correct">Correct guess!</p>';
            $_SESSION['hangman_correct_guesses'][] = $user_guess;
        } else {
            // Handle incorrect guesses
            if (!in_array($user_guess, $_SESSION['hangman_incorrect_guesses'])) {
                echo '<p class="hangman-incorrect">Incorrect guess. You guessed: ' . $user_guess . '</p>';
                $_SESSION['hangman_incorrect_guesses'][] = $user_guess;
                $_SESSION['hangman_attempts']++;
            } else {
                echo '<p class="hangman-incorrect">You already guessed the letter ' . $user_guess . ' incorrectly.</p>';
            }
        }
    }

    // Build the displayed word with correct and blank spaces for letters
    $displayed_word = '';
    for ($i = 0; $i < mb_strlen($_SESSION['hangman_word'], 'UTF-8'); $i++) {
        $letter = mb_substr($_SESSION['hangman_word'], $i, 1, 'UTF-8');
        if (in_array($letter, $_SESSION['hangman_correct_guesses']) || !ctype_alpha($letter)) {
            $displayed_word .= $letter . ' ';
        } else {
            $displayed_word .= '_ ';
        }
    }

    // Display game elements
    echo '<div id="hangman-game">';
    echo '<p id="current-word">Current word: ' . $displayed_word . '</p>';
    echo '<p id="incorrect-guesses">Incorrect guesses: ' . implode(', ', $_SESSION['hangman_incorrect_guesses']) . '</p>';
    echo '<p id="attempts-left">Attempts left: ' . (10 - $_SESSION['hangman_attempts']) . '</p>';

    // Check if the user has won
    if (str_replace(' ', '', $displayed_word) === $_SESSION['hangman_word']) {
        echo '<p class="hangman-win">Congratulations! You won!</p>';
        echo '<form method="post" action="">';
        echo '<input type="submit" name="reset_game" value="Play Again">';
        echo '</form>';
        session_destroy();
        echo '</div>';
        return;
    }

    // Check if the user has lost
    if ($_SESSION['hangman_attempts'] >= 10) {
        echo '<p class="hangman-lose">Sorry, you lost. The correct word was: ' . $_SESSION['hangman_word'] . '</p>';
        echo '<form method="post" action="">';
        echo '<input type="submit" name="reset_game" value="Play Again">';
        echo '</form>';
        session_destroy();
        echo '</div>';
        return;
    }

    // Display the form for guessing a letter
    echo '<form id="guess-form" method="post" action="">';
    echo '<label for="guess">Enter a letter: </label>';
    echo '<input type="text" name="guess" maxlength="1" required>';
    echo '<input type="submit" value="Guess">';
    echo '</form>';
    echo '</div>';
}
?>