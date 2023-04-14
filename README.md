# Franchise Plugin

This is a plugin to allow 1 database to power multiple sites individually whilst using the same code base.
This is done by applying a franchise_id field to database tables which the plugin will then only load those records.
A default website is required which will be treated as any records with a franchise_id of null will be loaded.
Records can also be set to be global so individual records can be accessible on all franchises e.g. a terms and conditions page may be the same across all franchises.

To make a model franchisable the following behaviour needs adding to the model.
```
Rejuvenate.Franchises.Behaviors.FranchisableModel
```

## Installation Instructions

Run the following to install this plugin:

```bash
php artisan plugin:install Dstokesy.Franchises --from=https://github.com/dstokesy/Franchise-Plugin
```

If you already have this plugin installed and need to update the database schema, run this command:

```bash
php artisan plugin:refresh Dstokesy.Franchises
```

To uninstall this plugin:

```bash
php artisan plugin:remove Dstokesy.Franchises
```

