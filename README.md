# Loic425LikeBundle

[**Symfony3**](http://symfony.com) integration of loic425 Like processing
component.

---

## Installation

  1. require the bundle with Composer:

  ```bash
  $ composer require loic425/like-bundle
  ```
  
  2. enable the bundle in `app/AppKernel.php`:

  ```php
  public function registerBundles()
  {
    $bundles = array(
      // ...
      new \Loic425\Bundle\LikeBundle\Loic425LikeBundle(),
      // ...
    );
  }
  ```

## License

This bundle is available under the [MIT license](LICENSE).
