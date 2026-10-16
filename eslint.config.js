export default [
  {
    ignores: ['node_modules/**', 'vendor/**', 'public/assets/**'],
  },
  {
    files: ['**/*.js'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
    },
    rules: {},
  },
];
