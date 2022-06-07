module.exports = {
    extends: 'airbnb-base',
    plugins: ['vue'],
    rules: {
        'indent': [1, 4, { 'SwitchCase': 1 } ],
        'semi': 0,
        'comma-dangle': 0,
        'object-shorthand': 2,
        'space-before-function-paren': [1, 'never'],
        'no-param-reassign': 0,
        'no-throw-literal': 0,
        'no-undef': 0,
        'func-names': 0,
        'no-unused-vars': 0,
        'no-underscore-dangle': 0,
        'quote-props': 0,
        'no-console': [1, { 'allow': ['error', 'warn'] } ],
        'new-cap': 0,
        'no-new': 0,
        'no-shadow': 0,
        'no-shadow-restricted-names': 0,
        'one-var': 0,
        'no-loop-func': 0,
        'no-alert': 0,
        'no-mutable-exports': 0,
        'import/no-mutable-exports': 0,
        'no-restricted-syntax': 1,
        'no-control-regex': 0,
        'no-extra-bind': 0,
        'yoda': 0,
        'no-cond-assign': 0,
        'no-extra-semi': 0,
        'default-case': 0,
        'wrap-iife': 0,
        'no-reserved-keys': 0,
        'import/no-unresolved': 0,
        'prefer-template': 1,
        'no-use-before-define': 1,
        'no-var': 0,
        // 'no-var': 1,
        'prefer-arrow-callback': ['error', { 'allowUnboundThis': false }], // Consider turning on
        'newline-per-chained-call': 0, // Consider turning on
        'consistent-return': 0, // Consider turning on
        'max-len': 0, // Consider turning on
        'radix': 0, // Consider turning on
        'vars-on-top': 0, // Consider turning on
        'array-callback-return': 0, // Consider turning on
        'no-else-return': 0, // Consider turning on
        'no-confusing-arrow': 0,
        'no-debugger': process.env.NODE_ENV === 'production' ? 2 : 0
    }
}
