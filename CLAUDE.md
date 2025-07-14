# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands
- Test email functionality: `php test_email.php`
- Run web server: Start Apache/Nginx with PHP support
- Database setup: Import SQL files from emailservice/ directory
- SMTP configuration: Set environment variables in .env file

## Code Style
- Follow PSR standards for PHP with descriptive naming
- Class names: PascalCase, functions/variables: camelCase or snake_case
- Use prepared statements for all database queries
- Separate HTML templates from PHP logic
- CSS: Use external stylesheets, follow BEM methodology
- HTML: Semantic markup, proper accessibility attributes
- Error handling: Use try/catch with specific exceptions
- Provide descriptive error messages with proper logging
- Document functions with PHPDoc comments

## Dependencies
- Core: PHP 8.0+, MySQL/MariaDB, Apache/Nginx
- Required PHP extensions: PDO, mysqli, mail, openssl
- Frontend: HTML5, CSS3, vanilla JavaScript
- Optional: Composer for package management

## Core Principles

The implementation must strictly adhere to these non-negotiable principles, as established in previous PRDs:

1. **DRY (Don't Repeat Yourself)**
   - Zero code duplication will be tolerated
   - Each functionality must exist in exactly one place
   - No duplicate files or alternative implementations allowed

2. **KISS (Keep It Simple, Stupid)**
   - Implement the simplest solution that works
   - No over-engineering or unnecessary complexity
   - Straightforward, maintainable code patterns

3. **Clean File System**
   - All existing files must be either used or removed
   - No orphaned, redundant, or unused files
   - Clear, logical organization of the file structure

4. **Transparent Error Handling**
   - No error hiding or fallback mechanisms that mask issues
   - All errors must be properly displayed to the user
   - Errors must be clear, actionable, and honest

## Success Criteria

In accordance with the established principles and previous PRDs, the implementation will be successful if:

1. **Zero Duplication**: No duplicate code or files exist in the codebase
2. **Single Implementation**: Each feature has exactly one implementation
3. **Complete Template System**: All HTML is generated via the template system
4. **No Fallbacks**: No fallback systems that hide or mask errors
5. **Transparent Errors**: All errors are properly displayed to users
6. **External Assets**: All CSS and JavaScript is in external files
7. **Component Architecture**: UI is built from reusable, modular components
8. **Consistent Standards**: Implementation follows UI_INTEGRATION_STANDARDS.md
9. **Full Functionality**: All features work correctly through template UI
10. **Complete Documentation**: Implementation details are properly documented
