# o2cms Newsletter Plugin

Pour cakePHP 1.3

## Installation

1. Copier le dossier `newsletter` dans `app/plugins/`
2. Copier le fichier `app/plugins/newsletter/config/plugins/newsletter.php.example` vers `app/config/plugins/newsletter.php`
3. Créer les dossiers newsletter et newsletter_box dans `app/views/elements`
4. Créer les tables dans la base de donnée (à l'aide du fichier `database.sql`)
5. Créer le dossier `app/webroot/files/newsletter` et mettre accessible en écriture.
6. Pour ajouter le menu Newsletter dans l'admin, ajouter le code suivant dans votre menu :
    ```php
    <?php echo $this->element('admin_mnu', array('plugin' => 'newsletter')); ?>
    ```
    
## Formulaire d'inscription

Utiliser une des méthodes suivante :

1. Mettre un liens vers `/newsletter/newsletter_email/add`
2. Mettre le formulaire directement dans le layout :
    ```php
    <?php 
       echo $form->create('NewsletterEmail',array('url'=>array('plugin'=>'newsletter')));
       // echo $form->hidden('redirect', array( // Pour une redirection apres l'inscription
       //   'value'=>$this->Html->url('MON-URL')
       // )); 
       // echo $form->hidden('sendlist_id', array('value'=>3)); // Pour choisir une liste de diffusion en particulier
       echo $form->input('name');
       echo $form->input('email');
       echo $form->end(__d('newsletter','Subscribe',true));
    ?>
    ```
    
## Création d'un template

Le fichier principale du template doit être créé dans `app/views/elements/newsletter` et s'apelle `<nom_du_template>.ctp`

Les contenu editable sont dans des boites (newsletter_box). Voir "Création de boites éditables"

### Pour ajouter une zone editable simple (une seule boite):

```php
<?php 
  echo $this->NewsletterMaker->single(
    1, // Numéro Unique
    'title', // Identifiant de la boite (newsletter_box)
    array( // Données par défault de la boite
      'title'=>__('Lorem ipsum',true),
    )
  );
?>
```

### Pour permettre l'ajouts d'une suite de boites 

```php
<?php 
  echo $this->NewsletterMaker->column(
    1, // Numéro Unique
    array(
      'allowedBox' => array('image','texte') //Nom des boites qu'il seras possible d'inséré
      // 'deniedBox' => array('<nom_box_1>','<nom_box_2>') //Si "deniedBox" est utiliser à la place de "allowedBox", toutes les boites pouront êtres utilisé sauf celles listé
      // 'separator' => '<tr><td>-----------------------</td></tr>' // Html to insert between rows
    )
  ); 
?>
```

### Lien voir dans le navigateur

```php
<a href="<?php echo $this->NewsletterMaker->viewUrl(); ?>"><?php __("Click here to see the newsletter in your browser") ?></a>
```

### Lien de desinscription

```php
<a href="<?php echo $this->NewsletterMaker->unsubscribeUrl(); ?>"><?php __("Click here to unsubscribe") ?></a>
```

## Création de boites éditables

Vous devez Créé 2 fichiers : `<nom_de_la_boite>.ctp` et `<nom_de_laboite>_edit.ctp` dans `/app/views/elements/newsletter_box`

### Formulaire d'édition

```php
<?php 
  echo $this->NewsletterMaker->createEditForm(); // Début du formulaire
  echo $this->NewsletterMaker->editInput('titre',array( // Champs texte
    'type' => 'text'
  )) 
  echo $this->NewsletterMaker->editInput('texte',array( // Champs RichText
    'type' => 'textarea',
    'class'=> 'tinymce'
  )) 
  echo $this->NewsletterMaker->editFileInput("pict"); // Upload d'image
	// echo $this->NewsletterMaker->editEntryFinder('NomDuModel'); // Permet de Choisir un item dans la BD. 
                                                                    Si l'item contient un champs au même nom nom que un dans le formulaire actuelle,
                                                                    le champ vas être automatiquement remplis lors de la selection
  echo $this->NewsletterMaker->endEditForm(); // fin du formulaire et bouton soumettre
?>
```

### Affichage

```php
<?php
	$titre = isset($newsletter_box["NewsletterBox"]["data"]['titre'])
            ? $newsletter_box["NewsletterBox"]["data"]['titre']
            : 'Lorem ipsum dolor';
	$texte = isset($newsletter_box["NewsletterBox"]["data"]['texte']) 
            ? $this->NewsletterMaker->filterRichtext($newsletter_box["NewsletterBox"]["data"]['texte'])
            : 'Lorem ipsum dolor sit amet';
  $pict = isset($newsletter_box["NewsletterBox"]["file"]["pict"]) 
            ? $newsletter_box["NewsletterBox"]["file"]["pict"]["path"] . $newsletter_box["NewsletterBox"]["file"]["pict"]["file"] 
            : null;
?>
<table>
	<tr>
		<td><b><?php echo $titre; ?></b></td>
	</tr>
	<tr>
		<td><?php echo $texte; ?></td>
	</tr>
  <tr>
		<td><img src="<?php echo $html->url($pict, true); ?>" alt="" /></td>
	</tr>
</table>
```

## À ne pas oublier

- Tous les liens doivent êtres créé avec la fonction url du helper newsletter. Ceci permet d'avoir des statistiques.
    ```php
    $this->NewsletterMaker->url(array('controller' => 'news', 'action' => 'index));
    ```
- Les images doivent être en absolu, on peut utiliser le paramêtre full de la fonction url() du helper Html
    ```php
    <img src="<?php echo $this->Html->url('/img/newsletter/logo.png', true); ?>" /> <!-- Notez le 2e paramêtre true -->
    ```
- Les champs texte tinymce devrai être formaté avec cette `filterRichtext` :
    ```php
    $this->NewsletterMaker->filterRichtext(
       $newsletter_box["NewsletterBox"]["data"]['texte'],
       array(
          // 'pToBr' => true // Optionnel : transforme les <p> en <br>
          // 'wrap' => array(
          //   'a'=>'<font color="#393939">%s</font>', // 'selector' => 'truc à ajouter'
          // )
       )
    )
    ```

