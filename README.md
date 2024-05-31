# Firestore ODM Bundle

A small bundle to persist and manage entities with Google Cloud Firestore Database in Symfony applications.

## Requirements

This bundle requires PHP 8.2+ and Symfony 7+.

This bundle also relies heavily on [Google Glouc Firestore PHP Client](https://cloud.google.com/php/docs/reference/cloud-firestore/latest), 
and its technical requirements are the same (most notably PHP gRPC and Protobuf extensions). See Google's documentation for more details.

## Installation

If you have Symfony Flex, a [contrib recipe](https://github.com/symfony/recipes-contrib) is available:

```shell
composer require tiriel/firestore-odm-bundle
```

That's it, you're good to go.

If you haven't got Flex installed, first require the bundle as shown above.
Then, enable the bundle in the `config/bundles.php` file:

```php
<?php

return [
    // ...
    Tiriel\FirestoreOdmBundle\TirielFirestoreOdmBundle::class => ['all' => true],
];

```

You can now create a `config/packages/tiriel.yaml` with the following content:

```yaml
tiriel_firestore_odm:
    # Replace the following values by your own
    project_name: my-project
    # Can be an env var, a string to a file, or a yaml array with the values from the credentials file
    service_account: '%kernel.project_dir%/config/secrets/my_project.json'
```

## Usage

### DTOs or Entities

Every DTO class in your application will create a new collection based on its FQCN in your Firestore database.

You can use whatever DTO you want as entities. The only requirement is that they implement 
`Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface` as a marker, and to ensure a `getId()` method 
is present. This `id` can be a string, integer, or Symfony `Uuid`.

Example:

```php
use Symfony\Component\Uid\Uuid;
use Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface;

class Image implements PersistableDtoInterface
{
    private ?Uuid $id = null;

    private ?string $path = null;
    
    //...
    
    public function getId(): Uuid|string
    {
        return $this->id;
    }

    // ...
```

You can now create a manager for your DTO.

### DTO Managers

To perform queries on your Firestore DTOs, you need to create a manager extending `Tiriel\FirestoreOdmBundle\Manager\FirestoreDtoManager`.
Inside, override the value of the `FirestoreDtoManager::DTO_CLASS` constant by specifying which DTO this manager is attached to:

```php
use App\Dto\Image;
use Tiriel\FirestoreOdmBundle\Manager\FirestoreDtoManager;

class ImageFirestoreDtoManager extends FirestoreDtoManager
{
    public const DTO_CLASS = Image::class;
}
```

And you're done, you can use your new DTO and its manager.

Internally, the bundle creates an alias for your Manager to be autowired as `DtoManagerInterface $dtoNameManager`.

Example with our `ImageFirestoreDtoManager`:

```php
use Tiriel\FirestoreOdmBundle\Manager\Interface\DtoManagerInterface;

class ImageController extends AbstractController
{
    #[Route('/images', name: 'app_image_index', methods: ['GET'])]
    public function index(DtoManagerInterface $imageManager): Response
    {
        // ...
```

#### Available methods

All managers provide you with the same interface and methods:

```php
interface DtoManagerInterface
{
    /**
     * Returns a single DTO matching the given $id 
     * 
     * @throws EntryNotFoundFirestoreException is the given id is not found
     */
    public function get(string $id): ?PersistableDtoInterface;

    /**
     * @return iterable the DTOs matching the given criteria
     * @param array $criteria 
     * Can be a single array matching Google SDK's `where` method, 
     * or an array of arrays:
     * 
     * $criteria = ['name', '=', 'foo']
     * or 
     * $criteria = [
     *      ['name', '=', 'foo'],
     *      ['createdAt', '>=', '01-01-1970'],
     *  ]
     */
    public function search(array $criteria): iterable;

    /**
     * @return iterable the full list of documents from the collection
     */
    public function getList(): iterable;

    /**
     * Persists a new entry in Firestore and generates a new id 
     * (Uuid v7 as of now)
     * 
     * @throws NonUniqueEntryFirestoreException if the generated Uuid is not unique
     */
    public function create(PersistableDtoInterface $dto): void;

    /**
     * @param PersistableDtoInterface $dto to be updated in the collection
     * @throws EntryNotFoundFirestoreException if the DTO's id doesn't exist in the collection
     */
    public function update(PersistableDtoInterface $dto): void;

    /**
     * @param PersistableDtoInterface $dto to be removed from the collection
     * @throws EntryNotFoundFirestoreException if the DTO's id doesn't exist in the collection
     */
    public function remove(PersistableDtoInterface $dto): void;

    /**
     * @return int the full count of documents in the collection
     */
    public function count(): int;

    /**
     * @return string the classname of the DTO associated to this manager
     */
    public function getClass(): string;
}
```

All managers also include a `protected CollectionReference $collection` object from `Google\Cloud\Firestore\CollectionReference`.
You can use it to create your own custom queries or pagination inside your Manager.

### Usage with Symfony Security

If you want to use Firestore to store the users for your security flow, you can check
my other bundle: [Firestore-Security-Bridge](https://github.com/Tiriel/firestore-security-bridge).

This package defines classes and User Providers to help you use Firestore as the source of your users.
