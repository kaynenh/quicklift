<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\Entities;
use Acquia\ContentHubClient\Entity;
use Acquia\ContentHubClient\Attribute;

// Fix Guzzle error.
if (!ini_get('date.timezone')) {
  date_default_timezone_set('America/New_York');
}

class QuickLift_CH_Manager
{

  // Set Config
  public $api_key = '';
  public $secret_key = '';
  public $api_host = '';
  public $client_name = '';
  public $client_id = '';
  public $connected = false;
  public $entities;

  public function __construct()
  {
    $options = get_option('quicklift_options');

    $this->api_host = $options['quicklift_ch_client_host'];
    $this->api_key = $options['quicklift_ch_client_api'];
    $this->secret_key = $options['quicklift_ch_client_secret'];
    $this->client_name = $options['quicklift_ch_client_name'];

    if ($this->loadConfig()) {
      $this->connected = true;

      $this->entities = new Entities();
      return true;
    }
    return false;
  }

  function loadConfig()
  {
    // Validate Acquia Content Hub connection.
    try {
      $client = new ContentHub($this->api_key, $this->secret_key, '', ['base_url' => $this->api_host]);
      $response = $client->getClientByName($this->client_name);
      $this->client_id = $response['uuid'];
    } catch (Exception $e) {
      if ($this->api_host != '' && $this->api_key != '' && $this->secret_key != '' && $this->client_name != '') {
        $this->quickLiftRegisterClient();
      } else {
        return false;
      }
    }

    return true;
  }

  function quickLiftListEntities()
  {
    if ($this->connected) {
      $entities = $this->quickLiftGetEntities();
      $rendered_list = '';
      if (!empty($entities)) {
        /*$mask = "%-36.36s | %-20.20s | %-36.36s | %-25.25s | %-25.25s<br />";
        $rendered_list = "Entities <br />";
        $rendered_list .= sprintf($mask, 'Content UUID', 'Type', 'Origin UUID', 'Modified', 'Action');
        $rendered_list .= sprintf($mask, str_repeat('-', 36), str_repeat('-', 20), str_repeat('-', 36), str_repeat('-', 25), str_repeat('-', 25));

        foreach ($entities as $entity) {
          $rendered_list .= sprintf($mask, $entity['uuid'], $entity['type'], $entity['origin'], $entity['modified'], "/wp-admin/options-general.php?page=quicklift-status.php?d=".$entity['uuid']);
        }*/


        $rendered_list = "<table>";
        $rendered_list .= "<tr><th>Content UUID</th><th>Type</th><th>Status</th><th>Origin UUID</th><th>Modified</th><th>Action</th></tr>";

        global $wpdb;
        foreach ($entities as $entity) {
          $status = 'disconnected';
          $results = $wpdb->get_results( "select A.post_id, A.meta_key, B.post_title from wp_postmeta A JOIN wp_posts B on A.post_id=B.ID where A.meta_value = '".$entity['uuid']."'", ARRAY_A );
          if (!empty($results)) {
            $status = "<a href='".get_edit_post_link($results[0]['post_id'])."'>".$results[0]['post_title']."</a>";
          }
          if ($entity['type'] == 'widget') {
            $status = 'not tracked';
          }
          $rendered_list .= "<tr><td>".$entity['uuid']."</td><td>".$entity['type']."</td><td>".$status."</td><td>".$entity['origin']."</td><td>".$entity['modified']."</td><td>"."<a href='/wp-admin/options-general.php?page=quicklift-status.php&d=".$entity['uuid']."'>Delete</a></td></tr>";
        }
        $rendered_list .= "</table>";
      } else {
        $rendered_list = "No entities found.\n";
      }

      return $rendered_list;
    }
  }

  function quickLiftGetEntities($clientOnly = TRUE)
  {
    $options = [];
    if ($clientOnly) {
      $options['origin'] = $this->client_id;
    }

    try {
      $client = new ContentHub($this->api_key, $this->secret_key, $this->client_id, ['base_url' => $this->api_host]);
      $request = $client->listEntities($options);
      if ($request['success']) {
        return $request['data'];
      }
    } catch (Exception $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  /**
   * Register new Acquia Content Hub client.
   *
   * @param $config
   */
  function quickLiftRegisterClient()
  {
    $client = new ContentHub($this->api_key, $this->secret_key, '', ['base_url' => $this->api_host]);

    try {
      $response = $client->register($this->client_name);
      $this->client_id = $response['uuid'];
    } catch (Exception $e) {
      throw new RuntimeException('Acquia Content Hub credentials could not be verified.');
    }
  }

  /**
   * Register new Acquia Content Hub client.
   *
   * @param $config
   */
  function quickLiftListClients()
  {
    try {
      $client = new ContentHub($this->api_key, $this->secret_key, $this->client_id, ['base_url' => $this->api_host]);
      $request = $client->getClients();
      if ($request['success']) {
        return $request['clients'];
      }
    } catch (Exception $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  /**
   * RunCreateEntities
   *
   * @param $config
   * @return Entities
   */
  function quickLiftRunCreateEntities() {
    // Process CSV.
    $entities = $this->quickLiftCreateEntity();

      // Save to Acquia Content Hub.
    $result = $this->quickLiftSaveEntities($entities);

      // Report results
    if ($result){
      echo "Entities Created";
    } else {
      throw new RuntimeException("An unknown error occurred.");
    }
  }

  /**
   * CreateEntities
   *
   * @param $config
   * @return Entity
   */
  function quickLiftCreateEntity($uuid = '', $id = 1,$type = 'undefined', $title = 'No title', $created = '', $modified = '', $preview_image = '', $html = 'No Content') {
    // Get default date.
    if ($created == '') {
      $created = date('c');
    }

    if ($modified == '') {
      $modified = date( 'c');
    }

    if ($type == 'widget') {

    }

    // Set entity
    $entity['uuid'] = $uuid;
    $entity['id'] = $id;
    $entity['type'] = $type;
    $entity['title'] = $title;
    $entity['created'] = $created;
    $entity['modified'] = $modified;
    $entity['preview_image'] = $preview_image;
    $entity['view_modes']['default'] = $html;

    // Build entity.
    $created_entity = $this->quickLiftBuildEntity($entity);
    $this->entities->addEntity($created_entity);

    return $created_entity;
  }


  /**
   * Build entity for Acquia Content Hub.
   *
   * @param $row
   * @param $config
   * @return Entity
   */
  function quickLiftBuildEntity($entity_data) {
    // Generate UUID.
    try {
      if ($entity_data['uuid'] == '') {
        $uuid = Uuid::uuid4()->toString();
        add_post_meta($entity_data['id'], 'lift_uuid', $uuid, true);
      } else {
        $uuid = $entity_data['uuid'];
      }
    } catch (UnsatisfiedDependencyException $e) {
      throw new RuntimeException($e->getMessage());
    }

    // Build the entity, add required metadata
    $entity = new Entity();
    $entity->setOrigin($this->client_id);
    $entity->setUuid($uuid);
    $entity->setType($entity_data['type']);
    $entity->setCreated($entity_data['created']);
    $entity->setModified($entity_data['modified']);

    // Add attributes
    $attribute = new Attribute(Attribute::TYPE_STRING);
    $attribute->setValue($entity_data['title']);
    $entity->setAttribute('title', $attribute);

    // Add metadata.
    $metadata = [
      'source' => [
        'cms' => 'Wordpress',
        'id' => $entity_data['id'],
      ],
    ];

    // Add view modes.
    if (!empty($entity_data['view_modes'])) {
      foreach ($entity_data['view_modes'] as $tpl => $html) {
        $metadata['view_modes'][$tpl] = [
          'html' => $html,
          'id' => $tpl,
          'label' => $tpl,
          'preview_image' => $entity_data['preview_image'],
          'url' => ''
        ];
      }
    }
    $entity->setMetaData($metadata);

    return $entity;
  }

  /**
   * Save entities to Acquia Content Hub.
   *
   * @param $entities
   * @param $config
   * @return mixed
   */
  function quickLiftSaveEntities($entities) {
    try {
      $client = new ContentHub($this->api_key, $this->secret_key, $this->client_id, ['base_url' => $this->api_host]);
      $request = $client->createEntities(NULL, $entities);

      return $request['success'];
    } catch (Exception $e) {
      throw new RuntimeException($e->getMessage());
    }
  }

  /**
   * Update entities in Acquia Content Hub.
   *
   * @param $entities
   * @param $config
   * @return mixed
   */
  function quickLiftUpdateEntities($entities) {
    try {
      $client = new ContentHub($this->api_key, $this->secret_key, $this->client_id, ['base_url' => $this->api_host]);
      $request = $client->updateEntities(NULL, $entities);
      return $request;
    } catch (Exception $e) {
      throw new RuntimeException($e->getMessage());
    }
  }


  /**
   * Delete entities from Acquia Content Hub.
   *
   * @param $entities
   * @param $config
   * @return mixed
   */
  function quickLiftDeleteEntity($uuid) {
    try {
      $client = new ContentHub($this->api_key, $this->secret_key, $this->client_id, ['base_url' => $this->api_host]);
      $request = $client->deleteEntity($uuid);
      return true;
    } catch (Exception $e) {
      throw new RuntimeException($e->getMessage());
    }
  }
}