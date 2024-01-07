<?php

namespace App\Util;

use App\Controllers\Controller;
use App\Models\WordModel;
use PDO;
use PDOException;

readonly class DatabaseHelper {

    private PDO $conn;

    public function __construct() {
        try {
            require Controller::getRoot() . "/app/login-data.php";
            $this->conn = new PDO("mysql:host={$server};port={$port};dbname={$dbname};charset=UTF8", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
        } catch (PDOException) {
            die("Connection error.");
        }
    }

    /**
     * @throws DatabaseException
     */
    public function getWord(string $word): ?WordModel {
        $statement = $this->conn->prepare("SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning FROM words WHERE word_directory = :word");
        $statement->execute(array("word" => $word));
        $results = $statement->fetchAll();

        if ($results === false) {
            throw new DatabaseException("Er ging iets fout.");
        }

        if (count($results) !== 1) {
            return null;
        }
        $wordEntry = $results[0];
        return new WordModel(...$wordEntry);
    }

    /**
     * @param string $letter
     * @return WordModel[]
     *
     * @throws DatabaseException
     */
    public function getWordsForLetter(string $letter): array {
        $statement = $this->conn->prepare("SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning FROM words WHERE word_directory LIKE CONCAT(:letter, '%')");
        $statement->execute(array("letter" => $letter));
        $results = $statement->fetchAll();

        if ($results === false) {
            throw new DatabaseException("Er ging iets fout.");
        }

        return array_map(static fn($result) => new WordModel(...$result), $results);
    }
}
