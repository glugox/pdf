# Magento 2 Catalog PDF Extension

#install

In your project's composer.json file under 'require' add:
<br />
<code>
"glugox/pdf": "*"
</code>

Under 'repositories' add this repo so the composer can get these files:
<br />
<code>{
    "type": "vcs",
    "url": "https://github.com/glugox/pdf"
}
</code>

At the time of writing this, you will probably have to change the minimum stabillity to dev:
<br />
<code>
"minimum-stability": "dev"
</code>

At the end simply run:
<br />
<code>
composer update
</code>
