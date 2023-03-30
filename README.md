# R4.01_ProjetAPIGestionBlog

Etudiants (Groupe B) : CAYROL Jules - RIEUNEAU Clément

Comptes et mots de passe pour utiliser l'application : 
  - User : Jules  / Mdp : Shrek / Role : Moderator
  - User : Kevin  / Mdp : Oui   / Role : Publisher
  - User : Loic   / Mdp : Mcdo  / Role : Publisher

URLs : 
  - APIBlog : http://localhost/R4.01_ProjetAPIGestionBlog-main/APIBlog.php
  - APIAuthentification : http://localhost/R4.01_ProjetAPIGestionBlog-main/APIAuthentification.php


Autres informations : Le client est utilisable mais non fini. Il est utilisable sous certaines conditions :
  - Il faut obligatoirement être connecté avec l'un des comptes
  - Les opérations non autorisés ( comme la supression d'un article qui n'est pas le sien en tant que publisher ) sont accessibles, mais non fontionnelles ( Exemple :  L'utilisateur publisher voit le bouton "supprimer" sur tous les articles mais lorsqu'il clique dessus il ne se passe rien si l'article n'est pas le sien. Sinon, si c'est son article il se supprime normalement )
  - Les erreurs ne sont pas traités dans le client
  - Pour pouvoir faire fonctionner les requêtes avec un type de compte, il faut regénérer un token avec l'APIAuthentification et ensuite mettre à jour le Bearer dans le header de la requête
  - Nos requêtes Postman ne couvre pas l'intégralité des requêtes de notre API, nous vous invitons à utiliser notre client incomplet pour essayer de faire plus de requêtes.
  - Pour ajouter un like ou un dislike, il faut envoyé "Like" ou "Dislike" dans le body
