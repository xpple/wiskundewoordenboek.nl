<?php

namespace App\Util;

use App\Controllers\Controller;
use App\Models\WordModel;
use PDO;
use PDOException;

readonly class DatabaseHelper {

    private PDO $conn;

    /**
     * @throws DatabaseException
     */
    public function __construct() {
        try {
            require Controller::getRoot() . "/app/login-data.php";
            $this->conn = new PDO("mysql:host={$server};port={$port};dbname={$dbname};charset=UTF8", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }

    /**
     * @param string $word
     * @return WordModel|null
     *
     * @throws DatabaseException
     */
    public function getWord(string $word): ?WordModel {
        try {
            $statement = $this->conn->prepare(<<<SQL
                SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning, word_formal_meaning
                FROM words
                WHERE word_directory = :word;
                SQL);
            $statement->execute(["word" => $word]);
            $results = $statement->fetchAll(PDO::FETCH_FUNC, static fn(...$args) => new WordModel(...$args));
            if (count($results) !== 1) {
                return null;
            }
            return $results[0];
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }

    /**
     * @param string $letter
     * @return WordModel[]
     *
     * @throws DatabaseException
     */
    public function getWordsForLetter(string $letter): array {
        try {
            $statement = $this->conn->prepare(<<<SQL
                SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning, word_formal_meaning
                FROM words
                WHERE word_capitalised LIKE CONCAT(:letter, '%')
                ORDER BY word_capitalised ASC;
                SQL);
            $statement->execute(["letter" => $letter]);
            return $statement->fetchAll(PDO::FETCH_FUNC, static fn(...$args) => new WordModel(...$args));
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }

    /**
     * @param string $query
     * @return WordModel[]
     *
     * @throws DatabaseException
     */
    public function getWordsForQuery(string $query): array {
        try {
            $statement = $this->conn->prepare(<<<SQL
                SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning, word_formal_meaning
                FROM words
                WHERE word_capitalised LIKE CONCAT('%', :query, '%')
                ORDER BY word_capitalised ASC;
                SQL);
            $statement->execute(["query" => $query]);
            return $statement->fetchAll(PDO::FETCH_FUNC, static fn(...$args) => new WordModel(...$args));
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }

    /**
     * @param string $word
     * @return string|null
     *
     * @throws DatabaseException
     */
    public function getPrimaryDirectoryForAlias(string $word): ?string {
        try {
            $statement = $this->conn->prepare(<<<SQL
                SELECT word_directory
                FROM words
                INNER JOIN directory_aliases ON words.word_id = directory_aliases.word_id
                WHERE directory_aliases.directory_alias = :word;
                SQL);
            $statement->execute(["word" => $word]);
            $results = $statement->fetchAll(PDO::FETCH_COLUMN);
            if (count($results) !== 1) {
                return null;
            }
            return $results[0];
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }

    /**
     * @param int $amount
     * @return WordModel[]
     *
     * @throws DatabaseException
     */
    public function getRecentlyAddedWords(int $amount): array {
        try {
            $statement = $this->conn->prepare(<<<SQL
                SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning, word_formal_meaning
                FROM words
                ORDER BY updated_at DESC
                LIMIT :amount;
                SQL);
            $statement->bindParam("amount", $amount, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_FUNC, static fn(...$args) => new WordModel(...$args));
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }

    /**
     * @param int $amount
     * @return WordModel[]
     *
     * @throws DatabaseException
     */
    public function getRandomWords(int $amount): array {
        try {
            $statement = $this->conn->prepare(<<<SQL
                SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning, word_formal_meaning
                FROM words
                ORDER BY rand()
                LIMIT :amount;
                SQL);
            $statement->bindParam("amount", $amount, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_FUNC, static fn(...$args) => new WordModel(...$args));
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }
}
