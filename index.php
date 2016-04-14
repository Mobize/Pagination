<?php
require_once 'db.php';

// 1. On définit la page sur laquelle on se trouve, par défaut 1 si le paramètre ?page= est vide ou pas défini dans l'url
// On force la variable $page en chiffre avec intval(), si l'utilisateur passe du texte au paramètre ?page= il sera converti en 0
$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
// On force $page à 1 si $page = 0
$page = $page > 0 ? $page : 1;

// 2. On définit le nombre d'éléments à afficher sur chaque page
$nb_items_per_page = 4;

// 3. On définit le nombre total d'éléments à paginer avec une requête SELECT COUNT() c.f. memos/pdo.php; memos/mysql.sql

// On renvoit un nom de colonne personnalisé avec as count_total
$query = $db->prepare('SELECT COUNT(*) as count_total FROM news');
$query->execute();
$result = $query->fetch();
// On va chercher en clé, le nom de colonne personnalisé renvoyé par le as, sinon on devrait aller chercher $result['COUNT(*)']
$count_total_items = $result['count_total'];

// 4. On construit une requête qui va chercher les éléments paginés avec LIMIT

// On définit le point de départ
$start = ($page - 1) * $nb_items_per_page;

$query = $db->prepare('SELECT * FROM news LIMIT :start, :nb_items');
// On calcul le point de départ
/*
Ex: Sur la page 1 : 0 * 10 = 0, on part de la ligne 0
    Sur la page 2 : 1 * 10 = 10, on part de la ligne 10
    Sur la page 3 : 2 * 10 = 20, on part de la ligne 20
    ...etc
*/
$query->bindValue('start', $start, PDO::PARAM_INT);
// On va chercher 10 lignes
$query->bindValue('nb_items', $nb_items_per_page, PDO::PARAM_INT);
$query->execute();
$results = $query->fetchAll();

// 5. On calcul le nombre de pages pour construire les liens de pagination

// On arrondit à l'entier supérieur avec ceil() pour gérer les pages restantes au delà de $count_total / $nb_items
// Ex: 142 / 10 = 14.2; ceil(14.2) = 15; On affiche 14 pages avec 10 éléments + 1 page avec 2 éléments
$nb_pages = ceil($count_total_items / $nb_items_per_page);


echo '<h1>Page '.$page.'</h1>';

echo '<p>On va chercher de la ligne <strong>'.$start.'</strong> à la ligne <strong>'.($start + $nb_items_per_page).'</strong></p>';

// 6. On affiche le titre de chaque article
foreach($results as $article) {

	echo '<a href="">'.$article['news_id'].'. '.$article['news_title'].'</a><br>';
}

echo '<hr>';

// 7. On construit la liste des liens de pagination
for ($i = 1; $i <= $nb_pages; $i++) {
	// Pour chaque lien, on transmet la page à un paramètre d'url ?page= (paramètre qu'on récupère à l'étape 1)
	// Et on affiche le numéro de la page
	echo '<a href="?page='.$i.'">'.$i.'</a> ';
}