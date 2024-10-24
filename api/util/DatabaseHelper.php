<?php

namespace api\util;

use app\controllers\Controller;
use app\models\SuggestedWordModel;
use app\models\WordChangeModel;
use app\models\WordModel;
use app\util\DatabaseException;
use PDO;
use PDOException;

readonly final class DatabaseHelper {

    private PDO $conn;

    /**
     * @throws DatabaseException
     */
    private function __construct() {
        try {
            require Controller::getRoot() . "/api/login-data.php";
            /**
             * @var string $server
             * @var string $port
             * @var string $dbname
             * @var string $user
             * @var string $pass
             */
            $this->conn = new PDO("mysql:host=$server;port=$port;dbname=$dbname;charset=UTF8", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }

    /**
     * @return DatabaseHelper
     *
     * @throws DatabaseException
     */
    public static function getInstance(): DatabaseHelper {
        static $databaseHelper = null;
        if ($databaseHelper === null) {
            $databaseHelper = new DatabaseHelper();
        }
        return $databaseHelper;
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

    /**
     * @throws DatabaseException
     */
    public function newWordSuggestion(string $name, string $sanitisedName, string $meaningOption, string $content, string $description, ?string $email): void {
        try {
            $statement = $this->conn->prepare(<<<SQL
                INSERT INTO suggested_words (word_capitalised, suggestion_directory, word_meaning_option, suggestion_content, suggestion_description, suggestor_email)
                VALUES (:name, CONCAT(:word_directory, '-', LPAD(FLOOR(RAND() * 99999999.99), 8, '0')), :meaning_option, :content, :description, :email);
                SQL);
            $result = $statement->execute(["name" => $name, "word_directory" => $sanitisedName, "meaning_option" => $meaningOption, "content" => $content, "description" => $description, "email" => $email]);
            if ($result === false) {
                throw DatabaseException::unknownError();
            }
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }

    /**
     * @param WordModel $wordModel
     * @param string $meaningOption
     * @param string $changes
     * @param string $description
     * @param string|null $email
     * @return void
     * @throws DatabaseException
     */
    public function newWordChange(WordModel $wordModel, string $meaningOption, string $changes, string $description, ?string $email): void {
        try {
            $statement = $this->conn->prepare(<<<SQL
                INSERT INTO word_changes (word_id, change_directory, word_meaning_option, change_content, change_description, changer_email)
                VALUES (UNHEX(:word_id), CONCAT(:word_directory, '-', LPAD(FLOOR(RAND() * 99999999.99), 8, '0')), :meaning_option, :changes, :description, :email)
                SQL);
            $result = $statement->execute(["word_id" => $wordModel->wordId, "word_directory" => $wordModel->wordDirectory, "meaning_option" => $meaningOption, "changes" => $changes, "description" => $description, "email" => $email]);
            if ($result === false) {
                throw DatabaseException::unknownError();
            }
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }

    /**
     * @param string $suggestion
     * @return SuggestedWordModel|null
     *
     * @throws DatabaseException
     */
    public function getWordSuggestion(string $suggestion): ?SuggestedWordModel {
        try {
            $statement = $this->conn->prepare(<<<SQL
                SELECT HEX(suggestion_id) as suggestion_id, suggestion_directory, word_capitalised, word_meaning_option, suggestion_content, suggestion_description, suggestor_email
                FROM suggested_words
                WHERE suggestion_directory = :suggestion;
                SQL);
            $statement->execute(["suggestion" => $suggestion]);
            $results = $statement->fetchAll(PDO::FETCH_FUNC, static fn(...$args) => new SuggestedWordModel(...$args));
            if (count($results) !== 1) {
                return null;
            }
            return $results[0];
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }

    /**
     * @param string $change
     * @return WordChangeModel|null
     *
     * @throws DatabaseException
     */
    public function getWordChange(string $change): ?WordChangeModel {
        try {
            $statement = $this->conn->prepare(<<<SQL
                SELECT HEX(change_id) as change_id, HEX(words.word_id) as word_id, words.word_directory, words.word_capitalised, words.word_meaning, words.word_formal_meaning, change_directory, word_meaning_option, change_content, change_description, changer_email
                FROM word_changes
                INNER JOIN words
                ON word_changes.word_id = words.word_id
                WHERE change_directory = :change;
                SQL);
            $statement->execute(["change" => $change]);
            $results = $statement->fetchAll(PDO::FETCH_FUNC, WordChangeModel::wrapper(...));
            if (count($results) !== 1) {
                return null;
            }
            return $results[0];
        } catch (PDOException) {
            throw DatabaseException::unknownError();
        }
    }
}
