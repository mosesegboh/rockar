module.exports = {
    roots: ['<rootDir>/test', '<rootDir>/features'],
    transform: {
        '^.+\\.tsx?$': 'ts-jest',
    },
    testMatch: ['**/*.steps.ts']
};