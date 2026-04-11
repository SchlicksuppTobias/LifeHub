<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

class Database
{
    private mysqli $conn;

    public function __construct(string $host, string $user, string $pass, string $dbname)
    {
        $this->conn = new mysqli($host, $user, $pass, $dbname);

        if ($this->conn->connect_error) {
            http_response_code(500);
            echo json_encode(['error' => 'DB-Verbindung fehlgeschlagen: ' . $this->conn->connect_error]);
            exit;
        }
    }

    public function getConnection(): mysqli
    {
        return $this->conn;
    }

    public function close(): void
    {
        $this->conn->close();
    }
}

class NutritionRepository
{
    public function __construct(private Database $db) {}

    public function getAll(): array
    {
        $result = $this->db->getConnection()->query(
            "SELECT * FROM NutritionContents ORDER BY name_de ASC LIMIT 100"
        );

        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'Abfrage fehlgeschlagen: ' . $this->db->getConnection()->error]);
            exit;
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function search(string $term): array
    {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT * FROM NutritionContents WHERE name_de LIKE ? ORDER BY name_de ASC LIMIT 100"
        );
        $like = '%' . $term . '%';
        $stmt->bind_param('s', $like);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'Abfrage fehlgeschlagen: ' . $this->db->getConnection()->error]);
            exit;
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

class NutritionController
{
    public function __construct(private NutritionRepository $repo) {}

    public function handle(): void
    {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $rows = $search !== ''
            ? $this->repo->search($search)
            : $this->repo->getAll();

        echo json_encode($rows);
    }
}

// ── Bootstrap ──────────────────────────────────────────────────────────
$db         = new Database('mariadb', 'root', 'root', 'LifeHub');
$repo       = new NutritionRepository($db);
$controller = new NutritionController($repo);

$controller->handle();

$db->close();