Of course. Here is the comprehensive note on test function naming conventions, formatted as a well-structured Markdown (`.md`) document that you can save and share.

You can copy the content below into a file named `test_naming_conventions.md`.

```markdown
# Test Naming Conventions: "it" vs. "when"

**Author:** Software Solution Architect & Senior Software Engineer
**Date:** October 26, 2023
**Topics:** Clean Code, Testing, BDD, AAA Pattern

---

## Executive Summary

For test function names, the correct and strongly recommended preposition is **`it`**. The `when` preposition is best used within `describe` blocks to set up context, not as the main test function.

*   **Use `it('should...')`** to define the expected behavior (the specification).
*   **Use `describe('when...')`** to group tests under a specific condition or state.

This approach aligns perfectly with Uncle Bob's Clean Code principles, the AAA (Arrange-Act-Assert) pattern, and modern Behavior-Driven Development (BDD) practices.

---

## 1. Core Principle: Tests as Specifications

The primary goal of a test name is to act as a readable, self-validating specification of the system's behavior. The name should complete the sentence: **"It should [expected behavior] under [certain conditions]."**

### The Right Way: `it`
```javascript
// This forms a complete, readable sentence:
it('should deduct the amount from the account balance on a valid withdrawal');
// "It should deduct the amount from the account balance on a valid withdrawal."
```
**✅ Advantage:** The outcome is clear without needing to read the test body. It describes the **postcondition**.

### The Incorrect Way: `when`
```javascript
// This is an incomplete sentence fragment:
when('a valid withdrawal is made');
// "When a valid withdrawal is made..." ...and then what?
```
**❌ Disadvantage:** It only describes the trigger (**the Act phase**), hiding the critical expected result. The reader must dig into the assertion to understand the specification.

---

## 2. Alignment with Clean Code & Testing Principles

### The AAA (Arrange-Act-Assert) Pattern
The `it` naming convention naturally maps to the final 'A'—**Assert**—which is the purpose of the test.

*   **Arrange:** Set up the context (e.g., `describe('with a balance of $100')` or inside the test).
*   **Act:** Perform the action (e.g., `describe('when a withdrawal is made')` or inside the test).
*   **Assert:** Verify the outcome (`it('should have a balance of $80')`).

### The Single Assertion Rule (Single Concept Rule)
A test should verify one logical concept or outcome. The `it` function name is ideal for describing that single, focused outcome.

```javascript
// This test has one clear purpose.
it('should return a 404 status code if the user is not found');
```

### The F.I.R.S.T. Principles
Clean tests are **F**ast, **I**ndependent, **R**epeatable, **S**elf-validating, and **T**imely. Using `it` creates a name that is **Self-Validating**; the expected behavior is explicitly stated in the function name itself.

---

## 3. The BDD (Behavior-Driven Development) Structure

Modern testing frameworks (Jest, Jasmine, Mocha, RSpec) are built on BDD principles. The hierarchical structure is key to organization and readability.

| Block | Purpose | Example |
| :--- | :--- | :--- |
| **`describe`** | Groups a suite of tests for a unit (e.g., a class). | `describe('Account')` |
| **`context`** / **`describe`** | Groups tests under a specific **state** or **condition** (this is where `when` shines). | `describe('with a zero balance')`<br>`describe('when an invalid PIN is provided')` |
| **`it`** | **The actual test case.** Defines the expected behavior. | `it('should throw an OverdraftError')` |

### Practical Example: Hierarchical Test Structure

```javascript
// Jest/Jasmine Example
describe('Account', () => {
  describe('with a balance of $100', () => { // Context: State
    describe('when making a withdrawal', () => { // Context: Action
      describe('of $20', () => { // Context: Parameter
        it('should deduct $20 from the balance', () => { // Spec
          // Arrange: account with $100
          // Act: withdraw $20
          // Assert: expect(balance).toBe(80);
        });
        it('should return $20 cash', () => { // Another spec for the same context
          // Assert: expect(cashDispensed).toBe(20);
        });
      });

      describe('of $120', () => { // A different context
        it('should not deduct any money', () => {
          // Assert: expect(balance).toBe(100);
        });
        it('should throw an InsufficientFundsError', () => {
          // Assert: expect(() => withdraw(120)).toThrow(InsufficientFundsError);
        });
      });
    });
  });
});
```

**Output in test runner:**
```
Account
  with a balance of $100
    when making a withdrawal
      of $20
        ✓ should deduct $20 from the balance
        ✓ should return $20 cash
      of $120
        ✓ should not deduct any money
        ✓ should throw an InsufficientFundsError
```

This structure produces beautiful, living documentation that is executable.

---

## 4. Conclusion & Final Recommendation

| Preposition | Recommended Use | Purpose |
| :--- | :--- | :--- |
| **`it`** | **Always** for test functions. | To specify the **expected behavior** (the outcome/assertion). |
| **`when`** | **Inside `describe` blocks.** | To define a **context, trigger, or precondition** for a group of tests. |

**Architect's Directive:** Enforce the use of `it('should...')` for all test cases. This creates a consistent, readable, and maintainable test suite that acts as definitive documentation for the system's behavior, fulfilling the goals of Clean Code architecture.
```

You can save this content directly to a `.md` file. This document serves as a complete reference for your team on this important naming convention.