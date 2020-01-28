# wp-rest-polylang

## description

Adds `polylang_translations` and keeps `lang` to WP REST api response for each Post and Page request for site running the Polylang Pro plugin.

## Values

### lang
The locale value of the post
```
{
  [...]
  "lang": "en"
  [...]
}
```

### translations
List of translation for the post
```
{
  [...]
  "polylang_translations": [
    {
      "lang": "fr",
      "id": 1
    }
  ],
  [...]
}
```
