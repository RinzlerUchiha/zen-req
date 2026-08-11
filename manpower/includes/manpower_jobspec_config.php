<?php
/**
 * Job Specification — Field & Table Config
 *
 * File: manpower/includes/manpower_jobspec_config.php
 *
 * Purpose: Single source of truth for the Job Spec form's field options
 * and DB column mapping. To retarget this whole feature to a different
 * table/column names later, only this file needs to change — the form,
 * save handler, and view all read from here instead of hardcoding.
 */

// Change this if the table is ever renamed/migrated
const MP_JOBSPEC_TABLE = 'tbl_manpower_jobspec';

// Delimiter used by the existing Zen Admin form for multi-select fields
// (e.g. "Extrovert%#Sensitive%#Feeling%#Perceiving"). Kept identical so
// HireFlow and Zen Admin stay compatible on the same table.
const MP_JOBSPEC_DELIM = '%#';

// Maps our internal field keys -> actual DB column names.
// Keeping this indirection means renaming a column later is a one-line change.
const MP_JOBSPEC_COLUMNS = [
    'id'               => 'jspec_id',
    'created_by'       => 'jspec_created_by',
    'department'       => 'jspec_department',
    'section'          => 'jspec_section',
    'position'         => 'jspec_position',
    'headcount'        => 'jspec_headsnum',
    'sex'              => 'jspec_sex',
    'agerange'         => 'jspec_agerange',
    'emplstat'         => 'jspec_emplstat',
    'education'        => 'jspec_education',
    'workexp'          => 'jspec_workexp',
    'duties'           => 'jspec_duties',
    'techcompetencies' => 'jspec_techcompetencies',
    'competencies'     => 'jspec_competencies',
    'computerskill'    => 'jspec_computerskill',
    'otherskill'       => 'jspec_otherskill',
    'mpa'              => 'jspec_mpa',
    'mpb'              => 'jspec_mpb',
    'mpc'              => 'jspec_mpc',
    'mpd'              => 'jspec_mpd',
    'mpe'              => 'jspec_mpe',
    'mpf'              => 'jspec_mpf',
    'mpg'              => 'jspec_mpg',
    'tapt'             => 'jspec_tapt',
    'enneagram'        => 'jspec_enneagram',
    'learnstyle'       => 'jspec_learnstyle',
    'career'           => 'jspec_career',
    'motivation'       => 'jspec_motivation',
    'personality'      => 'jspec_personality',
    'ravenl'           => 'jspec_ravenl',
    'ravena'           => 'jspec_ravena',
    'ravenh'           => 'jspec_ravenh',
    'leadership'       => 'jspec_leadership',
    'reason'           => 'jspec_reason',
    'remarks'          => 'jspec_remarks',
];

// Static option lists — mirrors the Blade form's checkboxes/radios exactly.
// 'detail' => true means the option has a free-text box next to it
// (e.g. "College Graduate: [Course/Degree]").
const MP_JOBSPEC_OPTIONS = [
    'education' => [
        ['value' => 'High School Graduate', 'detail' => false],
        ['value' => 'Vocational Course Graduate', 'detail' => false],
        ['value' => 'College Graduate (4 year course)', 'detail' => true],
        ['value' => 'Five-year course Graduate', 'detail' => true],
        ['value' => 'Masterate / Doctoral**Specify', 'detail' => true],
        ['value' => 'Licensed', 'detail' => true],
    ],
    'workexp' => [
        'Not Necessary (none)', '6 months to 1 year', '1 to 2 years', '2 years or more', '5 years or more',
    ],
    'computerskill' => [
        'Proficient in MS Office (Word, Excel, Power Point, Acces, Visio, etc. )',
        'Proficient in Accounting Software (Peach Tree, Quick Books, SAP, etc.)',
        'Layout Designing Skills (using Publisher, Corel, PageMaker etc.)',
    ],
    'mpa' => ['Towards', 'Away from', 'Both'],
    'mpb1' => ['Long-Term', 'Medium-Term', 'Short-Term'], // Terms
    'mpb2' => ['Past', 'Present', 'Future'],               // Time
    'mpc' => ['Internal', 'External', 'Both'],
    'mpd' => ['Match', 'Mismatch', 'Both'],
    'mpe' => ['Generalities', 'Details', 'Both'],
    'mpf1' => ['Choice', 'Procedure', 'Both'],             // Task
    'mpf2' => ['Self', 'Others', 'We, Both, Team'],        // Relationship
    'mpg' => ['Vision', 'Action', 'Emotion'],
    'tapt1' => ['Extrovert', 'Introvert'],
    'tapt2' => ['Sensitive', 'Intuitive'],
    'tapt3' => ['Thinking', 'Feeling'],
    'tapt4' => ['Judging', 'Perceiving'],
    'enneagram' => [
        'Perfectionist', 'Helper', 'Achiever', 'Romantic', 'Observer',
        'Questioner', 'Adventurer', 'Asserter', 'Peacemaker',
    ],
    'learnstyle' => ['Visual', 'Auditory', 'Kinesthetic'],
    'career' => [
        'Technical/Functional Competence', 'Autonomy/Independence', 'Entrepreneurial Creativity',
        'Pure Challenge', 'General/Managerial Competence', 'Security/ Stability',
        'Sense of Service/Dedication to A Cause', 'Lifestyle',
    ],
    'motivation' => [
        'Achievement', 'Personal Growth', 'Prestige', 'Family', 'Pleasure', 'Recognition',
        'Independence', 'Power', 'Security', 'Money', 'Pressure', 'Self-Esteem',
    ],
    'personality' => ['Controller', 'Analyst', 'Promoter', 'Supporter'],
    'ravenl' => ['Low', 'Average', 'High'],
    'ravena' => ['Low', 'Average', 'High'],
    'ravenh' => ['Low', 'Average', 'High'],
    'emplstat' => [
        'Apprentice - Sales', 'Contractual', 'Full-Time Faculty', 'Part-Time',
        'Part-Time Faculty', 'PartTime-FullLoad', 'Probationary', 'Probationary FullLoad', 'Regular',
    ],
];