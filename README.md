# R4.01_ProjetAPIGestionBlog

Etudiants (Groupe B) : CAYROL Jules - RIEUNEAU Clément

Comptes et mots de passe pour utiliser l'application : 
  - User : Jules  / Mdp : Shrek / Role : Moderator
  - User : Kevin  / Mdp : Oui   / Role : Publisher
  - User : Loic   / Mdp : Mcdo  / Role : Publisher

URLs : 
  - APIBlog : http://localhost/Projet-APIGestionBlog/R4.01_ProjetAPIGestionBlog/APIBlog.php
  - APIAuthentification : http://localhost/Projet-APIGestionBlog/R4.01_ProjetAPIGestionBlog/APIAuthentification.php


Autres informations : Le client est utilisable mais non fini. Il est utilisable sous certaines conditions :
  - Il faut obligatoirement être connecté avec l'un des comptes
  - Les opérations non autorisés ( comme la supression d'un article qui n'est pas le sien en tant que publisher ) sont accessibles, mais non fontionnelles ( Exemple :  L'utilisateur publisher voit le bouton "supprimer" sur tous les articles mais lorsqu'il clique dessus il ne se passe rien si l'article n'est pas le sien. Sinon, si c'est son article il se supprime normalement )
  - Les erreurs ne sont pas traités dans le client
