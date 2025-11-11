<?php

enum SubjectType: string
{
    case MAIN = 'Principales';
    case OTHERS = 'Autres';
    case HUMAS = 'Humanités';

    public static function fromString(string $type): SubjectType|null
    {
        return match ($type) {
            'main' => SubjectType::MAIN,
            'others' => SubjectType::OTHERS,
            'humas' => SubjectType::HUMAS,
            default => null,
        };
    }
}

enum SubjectColor: string
{
    case RED = '#CD0E14';
    case ORANGE = '#e3980e';
    case YELLOW = '#a29b14';
    case GREEN = '#008000';
    case BLUE = '#265fa3';
    case MAROON = '#893939';
    case GRAY = '#4c525c';
    case LIGHTGRAY = '#8d99ae';
    case PINK = '#e458a3';
    case PURPLE = '#990d9c';

    public static function fromString(string $color): SubjectColor|null
    {
        return match ($color) {
            'red' => SubjectColor::RED,
            'orange' => SubjectColor::ORANGE,
            'yellow' => SubjectColor::YELLOW,
            'green' => SubjectColor::GREEN,
            'blue' => SubjectColor::BLUE,
            'maroon' => SubjectColor::MAROON,
            'gray' => SubjectColor::GRAY,
            'lightgray' => SubjectColor::LIGHTGRAY,
            'pink' => SubjectColor::PINK,
            'purple' => SubjectColor::PURPLE,
            default => null,
        };
    }
}

function update_subject(mixed $id, mixed $name, mixed $color, mixed $type, $class_id): array
{
    $type = SubjectType::fromString($type);
    $color = SubjectColor::fromString($color);
    if ($type === null || $color === null) {
        return array("Type ou couleur invalide.");
    }
    if (strlen($name) > 16) {
        return array("Le nom de la matière ne doit pas dépasser 16 caractères.");
    }

    $q = getDB()->prepare("UPDATE agenda_subjects SET name=:name, color=:color, type=:type WHERE id=:id AND class_id=:class_id");
    $r = $q->execute([
        ":id" => $id,
        ":name" => $name,
        ":color" => strtolower($color->name),
        ":type" => strtolower($type->name),
        ":class_id" => $class_id
    ]);
    if (!$r) {
        return array("Erreur lors de la mise à jour de la matière.");
    }
    return array();
}

function delete_subject(mixed $id, $class_id): array
{
    $q = getDB()->prepare("UPDATE agenda_subjects SET is_deleted=1 WHERE id=:id AND class_id=:class_id");
    $r = $q->execute([
        ":id" => $id,
        ":class_id" => $class_id
    ]);
    if (!$r) {
        return array("Erreur lors de la suppression de la matière.");
    }
    return array();
}

function create_subject(mixed $name, mixed $color, mixed $type, $class_id): array
{
    $type = SubjectType::fromString($type);
    $color = SubjectColor::fromString($color);
    if ($type === null || $color === null) {
        return array("Type ou couleur invalide.");
    }
    if (strlen($name) > 16) {
        return array("Le nom de la matière ne doit pas dépasser 16 caractères.");
    }

    $q = getDB()->prepare("INSERT INTO agenda_subjects (name, color, type, class_id) VALUES (:name, :color, :type, :class_id)");
    $r = $q->execute([
        ":name" => $name,
        ":color" => strtolower($color->name),
        ":type" => strtolower($type->name),
        ":class_id" => $class_id
    ]);
    if (!$r) {
        return array("Erreur lors de l'ajout de la matière.");
    }
    return array();
}

function get_all_class_subjects($class_id)
{
    $q = getDB()->prepare("SELECT * FROM agenda_subjects WHERE class_id = :class_id ORDER BY type, name");
    $q->execute([
        'class_id' => $class_id
    ]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}
function extract_non_deleted_subjects($subjects)
{
    $non_deleted_subjects = [];
    foreach ($subjects as $subject) {
        if ($subject['is_deleted'] == 0) {
            $non_deleted_subjects[] = $subject;
        }
    }
    return $non_deleted_subjects;
}

function print_subjects_template_form($r = 'subjects')
{
    ?>
    <form method="post" action="<?= getRootPath() . 'agenda/manage/subjects' ?>" class="subject-templates">
        <input type="hidden" name="action" value="load_template">
        <input type="hidden" name="r" value="<?= $r ?>">
        <?php
        set_csrf();
        foreach (get_subjects_templates() as $name => $template){
            ?>
                <button type="submit" name="name" value="<?= $name ?>"><?= $name ?></button>
            <?php
        }
        ?>
    </form>
    <?php
}

function load_subjects_templates($class_id, $template_name)
{
    $template = get_subjects_templates()[$template_name];
    $q = getDB()->prepare("UPDATE agenda_subjects SET is_deleted=1 WHERE class_id = :class_id");
    $q->execute([
        'class_id' => $class_id
    ]);
    foreach ($template as $subject) {
        $q = getDB()->prepare("INSERT INTO agenda_subjects (name, color, type, class_id) VALUES (:name, :color, :type, :class_id)");
        $q->execute([
            ":name" => $subject['name'],
            ":color" => $subject['color'],
            ":type" => $subject['type'],
            ":class_id" => $class_id
        ]);
    }
}

function get_subjects_templates(): array
{
    return [
        'FIMI S1' => [
            [
                'name' => 'Maths', 'color' => 'red', 'type' => 'main'
            ], [
                'name' => 'OMNI', 'color' => 'blue', 'type' => 'main'
            ], [
                'name' => 'ISN', 'color' => 'yellow', 'type' => 'main'
            ], [
                'name' => 'Physique', 'color' => 'orange', 'type' => 'main'
            ], [
                'name' => 'Chimie', 'color' => 'green', 'type' => 'main'
            ], [
                'name' => 'Conception', 'color' => 'pink', 'type' => 'others'
            ], [
                'name' => 'SOL', 'color' => 'pink', 'type' => 'others'
            ], [
                'name' => 'Anglais', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'CSS', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'Autre', 'color' => 'gray', 'type' => 'others'
            ]
        ],
        'FIMI S2' => [
            [
                'name' => 'Maths', 'color' => 'red', 'type' => 'main'
            ], [
                'name' => 'OMNI', 'color' => 'blue', 'type' => 'main'
            ], [
                'name' => 'ISN', 'color' => 'yellow', 'type' => 'main'
            ], [
                'name' => 'Physique', 'color' => 'orange', 'type' => 'main'
            ], [
                'name' => 'Chimie', 'color' => 'green', 'type' => 'main'
            ], [
                'name' => 'Thermo', 'color' => 'maroon', 'type' => 'main'
            ], [
                'name' => 'Conception', 'color' => 'pink', 'type' => 'others'
            ], [
                'name' => 'Anglais', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'CE', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'ETRE', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'Autre', 'color' => 'gray', 'type' => 'others'
            ]
        ],
        'FIMI S3' => [
            [
                'name' => 'Maths', 'color' => 'red', 'type' => 'main'
            ],  [
                'name' => 'ISN', 'color' => 'yellow', 'type' => 'main'
            ], [
                'name' => 'Electromag', 'color' => 'orange', 'type' => 'main'
            ], [
                'name' => 'Mécanique', 'color' => 'blue', 'type' => 'main'
            ], [
                'name' => 'Chimie', 'color' => 'green', 'type' => 'main'
            ], [
                'name' => 'Conception', 'color' => 'pink', 'type' => 'main'
            ], [
                'name' => 'Anglais', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'CSS', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'ETRE', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'Autre', 'color' => 'gray', 'type' => 'others'
            ]
        ],
        'FIMI S4' => [
            [
                'name' => 'Maths', 'color' => 'red', 'type' => 'main'
            ],  [
                'name' => 'ISN', 'color' => 'yellow', 'type' => 'main'
            ], [
                'name' => 'Ondes', 'color' => 'orange', 'type' => 'main'
            ], [
                'name' => 'Mécanique', 'color' => 'blue', 'type' => 'main'
            ], [
                'name' => 'Anglais', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'CSS', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'ETRE', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'P2I', 'color' => 'green', 'type' => 'others'
            ]
        ],
        'GEn S1' => [
            [
                'name' => 'Maths', 'color' => 'blue', 'type' => 'main'
            ], [
                'name' => 'Conduction', 'color' => 'red', 'type' => 'main'
            ], [
                'name' => 'Stats', 'color' => 'blue', 'type' => 'others'
            ], [
                'name' => 'TTC', 'color' => 'red', 'type' => 'main'
            ], [
                'name' => 'Convection', 'color' => 'red', 'type' => 'main'
            ], [
                'name' => 'Rayonnement', 'color' => 'red', 'type' => 'main'
            ], [
                'name' => 'Enviro', 'color' => 'green', 'type' => 'others'
            ], [
                'name' => 'QualitéMilieux', 'color' => 'green', 'type' => 'others'
            ], [
                'name' => 'SystClimEn', 'color' => 'green', 'type' => 'others'
            ], [
                'name' => 'MécaFlu', 'color' => 'orange', 'type' => 'main'
            ], [
                'name' => 'GEnEPI', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'Anglais', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'LV2', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'Autre', 'color' => 'gray', 'type' => 'others'
            ]
        ],
    'GEn S2' => [
            [
                'name' => 'Thermo', 'color' => 'red', 'type' => 'main'
            ], [
                'name' => 'Commande', 'color' => 'blue', 'type' => 'main'
            ], [
                'name' => 'TDS', 'color' => 'green', 'type' => 'others'
            ], [
                'name' => 'ModMultiphysique', 'color' => 'blue', 'type' => 'others'
            ], [
                'name' => 'Hydraulique', 'color' => 'orange', 'type' => 'main'
            ], [
                'name' => 'TransfertMatière', 'color' => 'green', 'type' => 'main'
            ],  [
                'name' => 'GEnEPI', 'color' => 'purple', 'type' => 'humas'
            ],  [
                'name' => 'MesuresPhysiques', 'color' => 'blue', 'type' => 'others'
            ], [
                'name' => 'TraitEffluents', 'color' => 'green', 'type' => 'others'
            ], [
                'name' => 'Anglais', 'color' => 'purple', 'type' => 'humas'
            ], [
                'name' => 'AlgèbreLinéaire', 'color' => 'red', 'type' => 'main'
            ], [
                'name' => 'Autre', 'color' => 'gray', 'type' => 'others'
            ]
        ],
    ];
}
