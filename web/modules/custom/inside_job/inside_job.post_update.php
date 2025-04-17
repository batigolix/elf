<?php

/**
 * Post-update hook to execute a migration.
 */
function inside_job_post_update_run_migration(&$sandbox) {
  // The ID of the migration to execute.
    $migration_id = 'taxonomy_term_tags';

  // Load the migration plugin.
  $migration = \Drupal::service('plugin.manager.migration')->createInstance($migration_id);

  if ($migration) {
    // Create a migration executable.
    $executable = new \Drupal\migrate\MigrateExecutable(
      $migration,
      new \Drupal\migrate\MigrateMessage()
    );

    // Execute the migration.
    $executable->import();
  }
  else {
    throw new \Exception("Migration with ID $migration_id not found.");
  }
}
