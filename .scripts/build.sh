# Build ACTIONS-FILTERS.md
php create-actions-filters-docs.php

# Generate .pot file
php -n $(which wp) i18n make-pot ../ ../languages/woocommerce-convertkit.pot

# Build ZIP file
rm ../convertkit-for-woocommerce.zip
cd .. && zip -r convertkit-for-woocommerce.zip . -x "*.git*" -x ".scripts/*" -x ".wordpress-org/*" -x "tests/*" -x "vendor/*" -x "*.distignore" -x "*.env.*" -x "*codeception.*" -x "composer.json" -x "composer.lock" -x "*.md" -x "log.txt" -x "package-lock.json" -x "package.json" -x "phpcs.xml" -x "*.DS_Store"