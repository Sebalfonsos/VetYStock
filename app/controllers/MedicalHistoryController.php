<?php

class MedicalHistoryController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $animalId = (int) ($_GET['animal_id'] ?? 0);
        $animals = (new Animal())->allWithRelations();
        $pdo = Database::pdo();

        $treatments = [];
        $vaccines = [];
        $observations = [];

        if ($animalId > 0) {
            $stmt = $pdo->prepare('SELECT treatments.*, animals.name AS animal_name FROM treatments LEFT JOIN animals ON animals.id = treatments.animal_id WHERE treatments.animal_id = :animal_id ORDER BY treatments.treatment_date DESC');
            $stmt->execute(['animal_id' => $animalId]);
            $treatments = $stmt->fetchAll();

            $stmt = $pdo->prepare('SELECT vaccines.*, animals.name AS animal_name FROM vaccines LEFT JOIN animals ON animals.id = vaccines.animal_id WHERE vaccines.animal_id = :animal_id ORDER BY vaccines.application_date DESC');
            $stmt->execute(['animal_id' => $animalId]);
            $vaccines = $stmt->fetchAll();

            $stmt = $pdo->prepare('SELECT observations.*, animals.name AS animal_name FROM observations LEFT JOIN animals ON animals.id = observations.animal_id WHERE observations.animal_id = :animal_id ORDER BY observations.observation_date DESC');
            $stmt->execute(['animal_id' => $animalId]);
            $observations = $stmt->fetchAll();
        }

        $this->render('medical-history/index', [
            'title' => 'Historial médico',
            'animals' => $animals,
            'animalId' => $animalId,
            'treatments' => $treatments,
            'vaccines' => $vaccines,
            'observations' => $observations,
        ]);
    }
}

