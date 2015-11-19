var assert = require('assert');
var expect = require('chai').expect;

describe('Array', function() {
    describe('#indexOf()', function () {
        it('should return -1 when the value is not present', function () {
            assert.equal(-1, [1,2,3].indexOf(5));
            assert.equal(-1, [1,2,3].indexOf(0));
        });
    });
});

describe('number is immutable', function() {
    it('should pass', function () {
        "use strict";

        function add (state) {
            return state + 1
        }
        let state = 1;
        let newState = add(state);
        expect(newState).to.equal(state + 1);
    })
})