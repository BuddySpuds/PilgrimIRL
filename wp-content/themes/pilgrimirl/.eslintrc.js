module.exports = {
  extends: ['plugin:@wordpress/eslint-plugin/recommended'],
  env: {
    browser: true,
    es6: true,
    jquery: true
  },
  globals: {
    wp: true,
    google: true,
    pilgrimirl_ajax: true,
    PilgrimDebug: true
  },
  rules: {
    'no-console': 'warn',
    'no-unused-vars': 'warn',
    'prettier/prettier': 'off'
  }
};
