module.exports = {
  env: {
    es6: true,
    node: true,
  },
  extends: ['eslint:recommended', 'plugin:node/recommended', 'plugin:prettier/recommended'],
  parserOptions: {
    // Only ESLint 6.2.0 and later support ES2020.
    ecmaVersion: 2020,
    "sourceType": "module",
  },
  rules: {
    indent: ['error', 2, { SwitchCase: 1 }],
    quotes: ['warn', 'single'],
    semi: ['error', 'always'],
    'no-var': 'error',
    'no-console': 'off',
    'no-unused-vars': 'warn',
    'no-mixed-spaces-and-tabs': 'warn',
    'node/exports-style': ['error', 'module.exports'],
    'node/file-extension-in-import': ['error', 'always'],
    'node/prefer-global/buffer': ['error', 'always'],
    'node/prefer-global/console': ['error', 'always'],
    'node/prefer-global/process': ['error', 'always'],
    'node/prefer-global/url-search-params': ['error', 'always'],
    'node/prefer-global/url': ['error', 'always'],
    'node/prefer-promises/dns': 'error',
    'node/prefer-promises/fs': 'error',
    'node/no-unsupported-features/es-syntax': 'off',
  },
};
