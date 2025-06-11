<?php
/**
 * DatabaseSetup
 *
 * Connects to Feenix MariaDB, creates the `friends` and `myfriends`
 * tables if they do not exist, and populates them with sample data.
 */
class DatabaseSetup {
    private $conn;
    private $message = '';

    /**
     * Opens a connection to the specified database.
     */
    public function __construct($host, $user, $pswd, $dbnm) {
        $this->conn = new mysqli($host, $user, $pswd, $dbnm);
        if ($this->conn->connect_error) {
            $this->message = "Connection failed: " . $this->conn->connect_error;
        }
    }

    /**
     * Creates tables and inserts sample records.
     * @return string Status message.
     */
    public function setup() {
        if ($this->message) {
            return $this->message;
        }

        // Create `friends` table
        $sql1 = "
          CREATE TABLE IF NOT EXISTS friends (
            friend_id       INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
            friend_email    VARCHAR(50)   NOT NULL UNIQUE,
            password        VARCHAR(20)   NOT NULL,
            profile_name    VARCHAR(30)   NOT NULL,
            date_started    DATE          NOT NULL,
            num_of_friends  INT UNSIGNED  NOT NULL DEFAULT 0
          ) ENGINE=InnoDB;
        ";
        if (!$this->conn->query($sql1)) {
            return "Error creating friends table: " . $this->conn->error;
        }

        // Create `myfriends` table with self-friend check
        $sql2 = "
          CREATE TABLE IF NOT EXISTS myfriends (
            friend_id1 INT NOT NULL,
            friend_id2 INT NOT NULL,
            PRIMARY KEY (friend_id1, friend_id2),
            FOREIGN KEY (friend_id1) REFERENCES friends(friend_id),
            FOREIGN KEY (friend_id2) REFERENCES friends(friend_id),
            CONSTRAINT chk_no_self CHECK (friend_id1 <> friend_id2)
          ) ENGINE=InnoDB;
        ";
        if (!$this->conn->query($sql2)) {
            return "Error creating myfriends table: " . $this->conn->error;
        }

        // Populate `friends` if empty
        $res = $this->conn->query("SELECT COUNT(*) AS cnt FROM friends");
        if ($res && $res->fetch_assoc()['cnt'] == 0) {
            $sampleUsers = [
                ['amin@test.com',    'aminpwd',    'Amin',    '2022-01-10', 2],
                ['babu@test.com',    'babupwd',    'Babu',    '2022-01-11', 3],
                ['charu@test.com',   'charupwd',   'Charu',   '2022-01-12', 1],
                ['dalia@test.com',   'daliapwd',   'Dalia',   '2022-01-13', 4],
                ['eftem@test.com',   'eftempwd',   'Eftem',   '2022-01-14', 2],
                ['farid@test.com',   'faridpwd',   'Farid',   '2022-01-15', 0],
                ['gulshan@test.com', 'gulpwd',     'Gulshan', '2022-01-16', 5],
                ['habib@test.com',   'habibpwd',   'Habib',   '2022-01-17', 1],
                ['imran@test.com',   'imranpwd',   'Imran',   '2022-01-18', 3],
                ['judita@test.com',  'juditapwd',  'Judita',  '2022-01-19', 2],
            ];
            $stmt = $this->conn->prepare(
                "INSERT INTO friends
                 (friend_email, password, profile_name, date_started, num_of_friends)
                 VALUES (?, ?, ?, ?, ?)"
            );
            foreach ($sampleUsers as $u) {
                $stmt->bind_param("ssssi", $u[0], $u[1], $u[2], $u[3], $u[4]);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Populate `myfriends` if empty
        $res2 = $this->conn->query("SELECT COUNT(*) AS cnt FROM myfriends");
        if ($res2 && $res2->fetch_assoc()['cnt'] == 0) {
            $samplePairs = [
                [1,2],[1,3],[1,4],[2,3],[2,5],
                [3,6],[4,5],[4,7],[5,8],[6,9],
                [7,8],[7,10],[8,9],[9,10],[10,1],
                [2,6],[3,7],[4,8],[5,9],[6,10],
            ];
            $stmt2 = $this->conn->prepare(
                "INSERT IGNORE INTO myfriends (friend_id1, friend_id2) VALUES (?, ?)"
            );
            foreach ($samplePairs as $p) {
                $stmt2->bind_param("ii", $p[0], $p[1]);
                $stmt2->execute();
            }
            $stmt2->close();
        }

        return "Tables successfully created and populated.";
    }

    /**
     * Closes the database connection.
     */
    public function __destruct() {
        $this->conn->close();
    }
}
?>
