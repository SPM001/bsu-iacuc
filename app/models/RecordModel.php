<?php

require_once dirname(__DIR__) . '/core/Model.php';

class RecordModel extends Model
{
  private function buildFilters(
    string $search,
    string $school,
    string $animalType,
    string $gender,
    string $researcherType
  ): array {
    $conditions = [];
    $params     = [];
    $types      = '';

    if ($search !== '') {
      $like = '%' . $search . '%';
      $conditions[] = "(reference_no LIKE ? OR title_of_research LIKE ? OR school LIKE ?
                              OR animal_type LIKE ? OR principal_investigator LIKE ?
                              OR gender LIKE ? OR researcher_type LIKE ?
                              OR research_adviser LIKE ? OR veterinarian LIKE ?
                              OR research_duration LIKE ? OR received_by LIKE ?)";
      for ($i = 0; $i < 11; $i++) {
        $params[] = &$like;
        $types   .= 's';
      }
    }
    if ($school !== '') {
      $conditions[] = 'school = ?';
      $params[] = &$school;
      $types .= 's';
    }
    if ($animalType !== '') {
      $conditions[] = 'animal_type = ?';
      $params[] = &$animalType;
      $types .= 's';
    }
    if ($gender !== '') {
      $conditions[] = 'gender = ?';
      $params[] = &$gender;
      $types .= 's';
    }
    if ($researcherType !== '') {
      $conditions[] = 'researcher_type = ?';
      $params[] = &$researcherType;
      $types .= 's';
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    return [$where, $params, $types];
  }

  public function getAll(
    string $search = '',
    string $school = '',
    string $animalType = '',
    string $gender = '',
    string $researcherType = '',
    int $limit = 25,
    int $offset = 0
  ): array {
    [$where, $params, $types] = $this->buildFilters($search, $school, $animalType, $gender, $researcherType);

    $stmt = $this->connection->prepare(
      "SELECT * FROM `records` $where ORDER BY date_released DESC, reference_no DESC LIMIT ? OFFSET ?"
    );
    if (! $stmt) return [];

    $params[] = &$limit;
    $types .= 'i';
    $params[] = &$offset;
    $types .= 'i';

    if ($types) {
      array_unshift($params, $types);
      call_user_func_array([$stmt, 'bind_param'], $params);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function count(
    string $search = '',
    string $school = '',
    string $animalType = '',
    string $gender = '',
    string $researcherType = ''
  ): int {
    [$where, $params, $types] = $this->buildFilters($search, $school, $animalType, $gender, $researcherType);

    $stmt = $this->connection->prepare("SELECT COUNT(*) FROM `records` $where");
    if (! $stmt) return 0;

    if ($types) {
      array_unshift($params, $types);
      call_user_func_array([$stmt, 'bind_param'], $params);
    }

    $stmt->execute();
    return (int) $stmt->get_result()->fetch_row()[0];
  }

  public function distinctValues(string $column): array
  {
    $allowed = ['school', 'animal_type', 'gender', 'researcher_type'];
    if (! in_array($column, $allowed, true)) return [];

    $result = $this->connection->query(
      "SELECT DISTINCT `$column` FROM `records`
             WHERE `$column` IS NOT NULL AND `$column` != ''
             ORDER BY `$column`"
    );
    if (! $result) return [];

    return array_column($result->fetch_all(MYSQLI_ASSOC), $column);
  }

  public function getById(int $id): ?array
  {
    $stmt = $this->connection->prepare("SELECT * FROM `records` WHERE id = ?");
    if (! $stmt) return null;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
  }

  public function exists(int $id): bool
  {
    $stmt = $this->connection->prepare("SELECT 1 FROM `records` WHERE id = ?");
    if (! $stmt) return false;
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_row();
  }

  public function refExists(string $ref): bool
  {
    $stmt = $this->connection->prepare("SELECT 1 FROM `records` WHERE reference_no = ?");
    if (! $stmt) return false;
    $stmt->bind_param('s', $ref);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_row();
  }

  public function insert(array $d): bool
  {
    $stmt = $this->connection->prepare(
      "INSERT INTO `records`
             (reference_no, title_of_research, school, animal_type, animal_count,
              principal_investigator, gender, researcher_type, research_adviser,
              veterinarian, research_duration, date_released, received_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    if (! $stmt) return false;

    $animalCount = isset($d['animal_count']) && $d['animal_count'] !== '' ? (int)$d['animal_count'] : null;
    $dateReleased = $d['date_released'] !== '' ? $d['date_released'] : null;

    $stmt->bind_param(
      'ssssissssssss',
      $d['reference_no'],
      $d['title_of_research'],
      $d['school'],
      $d['animal_type'],
      $animalCount,
      $d['principal_investigator'],
      $d['gender'],
      $d['researcher_type'],
      $d['research_adviser'],
      $d['veterinarian'],
      $d['research_duration'],
      $dateReleased,
      $d['received_by']
    );
    return $stmt->execute();
  }

  public function insertFromProtocol(string $refNo, string $title, string $pi): bool
  {
    if ($refNo !== '' && $this->refExists($refNo)) {
      return false;
    }

    $refNoOrNull = $refNo !== '' ? $refNo : null;

    $stmt = $this->connection->prepare(
      "INSERT INTO `records` (reference_no, title_of_research, principal_investigator, school)
             VALUES (?, ?, ?, '')"
    );
    if (! $stmt) return false;
    $stmt->bind_param('sss', $refNoOrNull, $title, $pi);
    return $stmt->execute();
  }

  public function update(array $d): bool
  {
    $stmt = $this->connection->prepare(
      "UPDATE `records` SET
               reference_no           = ?,
               title_of_research      = ?,
               school                 = ?,
               animal_type            = ?,
               animal_count           = ?,
               principal_investigator = ?,
               gender                 = ?,
               researcher_type        = ?,
               research_adviser       = ?,
               veterinarian           = ?,
               research_duration      = ?,
               date_released          = ?,
               received_by            = ?
             WHERE id = ?"
    );
    if (! $stmt) return false;

    $ref          = $d['reference_no'] !== '' ? $d['reference_no'] : null;
    $animalCount  = isset($d['animal_count']) && $d['animal_count'] !== '' ? (int)$d['animal_count'] : null;
    $dateReleased = $d['date_released'] !== '' ? $d['date_released'] : null;

    $stmt->bind_param(
      'ssssissssssssi',
      $ref,
      $d['title_of_research'],
      $d['school'],
      $d['animal_type'],
      $animalCount,
      $d['principal_investigator'],
      $d['gender'],
      $d['researcher_type'],
      $d['research_adviser'],
      $d['veterinarian'],
      $d['research_duration'],
      $dateReleased,
      $d['received_by'],
      $d['id']
    );
    return $stmt->execute();
  }

  public function delete(int $id): bool
  {
    $stmt = $this->connection->prepare("DELETE FROM `records` WHERE id = ?");
    if (! $stmt) return false;
    $stmt->bind_param('i', $id);
    return $stmt->execute();
  }
}
