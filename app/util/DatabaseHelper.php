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
        $statement = $this->conn->prepare(<<<SQL
            SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning, word_formal_meaning
            FROM words
            WHERE word_directory = :word;
            SQL);
        $statement->execute(array("word" => $word));
        $results = $statement->fetchAll();

        if ($results === false) {
            throw DatabaseException::unknownError();
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
        $statement = $this->conn->prepare(<<<SQL
            SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning, word_formal_meaning
            FROM words
            WHERE word_capitalised LIKE CONCAT(:letter, '%')
            ORDER BY word_capitalised ASC;
            SQL);
        $statement->execute(array("letter" => $letter));
        $results = $statement->fetchAll();

        if ($results === false) {
            throw DatabaseException::unknownError();
        }

        return array_map(static fn($result) => new WordModel(...$result), $results);
    }

    /**
     * @param string $query
     * @return WordModel[]
     *
     * @throws DatabaseException
     */
    public function getWordsForQuery(string $query): array {
        $statement = $this->conn->prepare(<<<SQL
            SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning, word_formal_meaning
            FROM words
            WHERE word_capitalised LIKE CONCAT('%', :query, '%')
            ORDER BY word_capitalised ASC;
            SQL);
        $statement->execute(array("query" => $query));
        $results = $statement->fetchAll();

        if ($results === false) {
            throw DatabaseException::unknownError();
        }

        return array_map(static fn($result) => new WordModel(...$result), $results);
    }

    /**
     * @param string $word
     * @return string|null
     *
     * @throws DatabaseException
     */
    public function getPrimaryDirectoryForAlias(string $word): ?string {
        $statement = $this->conn->prepare(<<<SQL
            SELECT word_directory
            FROM words
            INNER JOIN directory_aliases ON words.word_id = directory_aliases.word_id
            WHERE directory_aliases.directory_alias = :word;
            SQL);
        $statement->execute(array("word" => $word));
        $results = $statement->fetchAll(PDO::FETCH_COLUMN);

        if ($results === false) {
            throw DatabaseException::unknownError();
        }

        if (count($results) !== 1) {
            return null;
        }
        return $results[0];
    }

    /**
     * @param int $amount
     * @return WordModel[]
     *
     * @throws DatabaseException
     */
    public function getRecentlyAddedWords(int $amount): array {
        $statement = $this->conn->prepare(<<<SQL
            SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning, word_formal_meaning
            FROM words
            ORDER BY updated_at DESC
            LIMIT :amount;
            SQL);
        $statement->bindParam("amount", $amount, PDO::PARAM_INT);
        $statement->execute();
        $results = $statement->fetchAll();

        if ($results === false) {
            throw DatabaseException::unknownError();
        }

        return array_map(static fn($result) => new WordModel(...$result), $results);
    }

    /**
     * @param int $amount
     * @return WordModel[]
     *
     * @throws DatabaseException
     */
    public function getRandomWords(int $amount): array {
        $statement = $this->conn->prepare(<<<SQL
            SELECT HEX(word_id) as word_id, word_directory, word_capitalised, word_meaning, word_formal_meaning
            FROM words
            ORDER BY rand()
            LIMIT :amount;
            SQL);
        $statement->bindParam("amount", $amount, PDO::PARAM_INT);
        $statement->execute();
        $results = $statement->fetchAll();

        if ($results === false) {
            throw DatabaseException::unknownError();
        }

        return array_map(static fn($result) => new WordModel(...$result), $results);
    }
}
