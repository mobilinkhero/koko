<?php

namespace Corbital\Installer\Classes;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PDOException;

class DatabaseTest
{
    /**
     * The last error occurred during database tests.
     */
    protected ?string $lastError = null;

    /**
     * Test table name.
     */
    protected string $testTable = 'test_table';

    /**
     * Database connection instance.
     */
    protected Connection $connection;

    /**
     * Test tables to clean up.
     */
    protected array $tablesToCleanup = [];

    /**
     * Initialize new DatabaseTest instance.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->tablesToCleanup = [$this->testTable, 'test_users', 'test_user_relations'];
    }

    /**
     * Run a specific database privilege test.
     *
     * @param  string  $testName  The name of the test for error reporting
     * @param  Closure  $testFunction  The function containing test logic
     * @return bool Whether the test passed
     */
    public function runTest(string $testName, Closure $testFunction): bool
    {
        try {
            $testFunction();

            return true;
        } catch (QueryException|PDOException $e) {
            $this->lastError = "[$testName] ".$e->getMessage();

            return false;
        } finally {
            $this->cleanup();
        }
    }

    /**
     * Test DROP privilege.
     */
    public function testDropTable(): bool
    {
        return $this->runTest('DROP', function () {
            $this->dropTable();
        });
    }

    /**
     * Test CREATE privilege.
     */
    public function testCreateTable(): bool
    {
        return $this->runTest('CREATE', function () {
            $this->createTable();
        });
    }

    /**
     * Test INSERT privilege.
     */
    public function testInsert(): bool
    {
        return $this->runTest('INSERT', function () {
            $this->createTable();
            $this->insertRow();
        });
    }

    /**
     * Test SELECT privilege.
     */
    public function testSelect(): bool
    {
        return $this->runTest('SELECT', function () {
            $this->createTable();
            $this->insertRow();

            DB::usingConnection($this->connection->getName(), function () {
                DB::table($this->testTable)->get();
            });
        });
    }

    /**
     * Test UPDATE privilege.
     */
    public function testUpdate(): bool
    {
        return $this->runTest('UPDATE', function () {
            $this->createTable();
            $this->insertRow();

            DB::usingConnection($this->connection->getName(), function () {
                DB::table($this->testTable)->update(['test_column' => 'Corbital Updated']);
            });
        });
    }

    /**
     * Test DELETE privilege.
     */
    public function testDelete(): bool
    {
        return $this->runTest('DELETE', function () {
            $this->createTable();
            $this->insertRow();

            DB::usingConnection($this->connection->getName(), function () {
                DB::table($this->testTable)->delete();
            });
        });
    }

    /**
     * Test ALTER privilege.
     */
    public function testAlter(): bool
    {
        return $this->runTest('ALTER', function () {
            $this->createTable();

            $tableName = $this->withTablePrefix();
            $this->connection->getPdo()->exec(
                "ALTER TABLE {$tableName} ADD COLUMN new_column VARCHAR(100)"
            );
        });
    }

    /**
     * Test INDEX privilege.
     */
    public function testIndex(): bool
    {
        return $this->runTest('INDEX', function () {
            $this->createTable();

            $tableName = $this->withTablePrefix();
            $this->connection->getPdo()->exec(
                "CREATE INDEX test_column_index ON {$tableName} (test_column(10))"
            );
        });
    }

    /**
     * Test REFERENCES privilege.
     */
    public function testReferences(): bool
    {
        return $this->runTest('REFERENCES', function () {
            // Create users table with primary key
            $this->createTable('test_users', [
                'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
                'name VARCHAR(255) NOT NULL',
            ]);

            // Create relations table with foreign key
            $this->createTable('test_user_relations', [
                'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
                'test_user_id BIGINT UNSIGNED',
                'FOREIGN KEY (test_user_id) REFERENCES test_users(id)',
            ]);
        });
    }

    /**
     * Run all database privilege tests.
     *
     * @return array Results with test name as key and boolean result as value
     */
    public function runAllTests(): array
    {
        return [
            'DROP' => $this->testDropTable(),
            'CREATE' => $this->testCreateTable(),
            'INSERT' => $this->testInsert(),
            'SELECT' => $this->testSelect(),
            'UPDATE' => $this->testUpdate(),
            'DELETE' => $this->testDelete(),
            'ALTER' => $this->testAlter(),
            'INDEX' => $this->testIndex(),
            'REFERENCES' => $this->testReferences(),
        ];
    }

    /**
     * Get the last test error.
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Clean up by dropping all test tables.
     */
    protected function cleanup(): void
    {
        foreach ($this->tablesToCleanup as $table) {
            $this->dropTable($table);
        }
    }

    /**
     * Drop table.
     */
    protected function dropTable(?string $tableName = null): void
    {
        try {
            $pdo = $this->connection->getPdo();
            $tableName = $tableName ?: $this->testTable;
            $tableName = $this->withTablePrefix($tableName);

            $pdo->exec("DROP TABLE IF EXISTS {$tableName}");
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
        }
    }

    /**
     * Create a table with specified columns.
     *
     * @param  string|null  $tableName  The table name (without prefix)
     * @param  array  $columns  Column definitions
     */
    protected function createTable(?string $tableName = null, array $columns = []): void
    {
        if (empty($columns)) {
            $columns = [
                'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
                'test_column VARCHAR(255) NOT NULL',
            ];
        }

        $tableName = $tableName ?: $this->testTable;
        $tableName = $this->withTablePrefix($tableName);
        $columnDefinitions = implode(', ', $columns);

        try {
            $pdo = $this->connection->getPdo();
            $pdo->exec("CREATE TABLE IF NOT EXISTS {$tableName} ({$columnDefinitions})");
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
        }
    }

    /**
     * Insert test row in the test table.
     */
    protected function insertRow(?string $tableName = null): void
    {
        try {
            $pdo = $this->connection->getPdo();
            $tableName = $this->withTablePrefix($tableName ?: $this->testTable);

            $stmt = $pdo->prepare("INSERT INTO {$tableName} (test_column) VALUES (?)");
            $stmt->execute(['Corbital']);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
        }
    }

    /**
     * Ensure the table name has the connection's prefix.
     */
    protected function withTablePrefix(?string $tableName = null): string
    {
        return $this->connection->getTablePrefix().($tableName ?? $this->testTable);
    }
}
