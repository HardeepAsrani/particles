# CLAUDE.md - Project Particles AI Assistant Instructions

## Project Overview

**Project Particles** is a WordPress plugin that provides an AI-powered chat interface for generating WordPress Block Patterns directly within the Site Editor and Block Editor. Users describe what they want to build in natural language, and the AI generates complete, theme-aware patterns using only core WordPress blocks.

## Core Constraints

### WordPress Requirements
- **Blocks**: Use ONLY core WordPress blocks (no custom blocks)
- **Output**: Generate WordPress Block Patterns (HTML comment format)
- **Styling**: All styles must come from theme.json configuration
- **Editors**: Plugin works within WordPress Site Editor and Block Editor
- **WordPress Version**: Target WordPress 6.0+ for full Site Editor support

### Technical Stack
- **Backend**: PHP (WordPress coding standards)
- **Frontend**: React (WordPress Gutenberg components preferred)
- **UI Components**: @wordpress/components library or shadcn/ui
- **Build Tools**: WordPress scripts (@wordpress/scripts)
- **Icons**: lucide-react or @wordpress/icons

## AI Flow Architecture

### Initial Pattern Generation (2 AI Requests)

#### Request 1: Planning Phase
**Purpose**: Analyze user request and create structured outline

**AI receives:**
- User's description of what they want to build
- System prompt explaining role and constraints

**AI returns:**
```json
{
  "outline": {
    "page_type": "landing_page",
    "subject": "Brief description",
    "sections": [
      {
        "id": "unique_id",
        "name": "Section Name",
        "description": "What this section does",
        "components": ["block_types", "needed"]
      }
    ],
    "estimated_blocks": 25
  }
}
```

**User sees**: Formatted outline of sections to be created

#### Request 2: Generation Phase
**Purpose**: Generate complete WordPress block pattern

**AI receives:**
- Outline from planning phase
- theme.json data (colors, typography, spacing, layout)
- System prompt for block generation

**AI returns:**
- Complete WordPress block pattern markup
- All top-level blocks include `context` attribute
- Content generated based on topic understanding (placeholder content is acceptable)

**Context Attribute Specification:**
- Added to ALL top-level blocks (groups and standalone blocks)
- Format: Free-form text, medium detail level
- Content: Purpose + key design decisions + content intent
- Example: `"Hero section introducing Audi F1 with dramatic black background, emphasizing 2026 debut with clear CTA"`
- Should avoid sensitive information
- No character limit - AI decides based on complexity

### Iterative Refinements (1 AI Request)

**Purpose**: Modify existing pattern based on user feedback

**AI receives:**
- User's refinement request
- Complete current pattern (all sections with context attributes)
- Conversation history (initial request + last 5-10 messages)
- theme.json data

**AI returns:**
- Complete updated pattern with all modifications applied
- Context attributes updated where changes are significant
- Optional explanation message

**Request Types Handled:**
1. Modify existing section (change properties)
2. Add new section
3. Remove section
4. Reorder sections
5. Multi-part requests (multiple changes at once)

**Edge Cases:**
- **Ambiguous requests**: Use conversation history to infer, or ask clarification
- **Impossible requests**: Do best approximation + explain limitations
- **Theme limitations**: Suggest alternatives from available theme.json options

## Frontend Architecture

### Chat Interface Components

#### Layout Structure
```
┌─────────────────────────────────┬──────────────┐
│                                 │   Chat UI    │
│      Editor Preview Area        │   Sidebar    │
│      (WordPress Editor)         │   (384px)    │
│                                 │              │
└─────────────────────────────────┴──────────────┘
```

#### Message Types
1. **System Messages**: Welcome, tips, status updates
2. **User Messages**: User's input/requests
3. **AI Outline**: Structured plan before generation
4. **AI Loading**: Progress indicators during processing
5. **AI Complete**: Success message with action buttons

#### Required States
- Empty state (no messages)
- Loading state (AI processing)
- Outline displayed (planning complete)
- Pattern generated (ready to insert/refine)
- Error state (API failure, invalid request)

#### Action Buttons
- **Insert Pattern**: Add pattern to WordPress editor
- **Refine Design**: Continue conversation to modify
- **Start Over**: Clear and begin new pattern
- **Close Panel**: Dismiss chat interface

### User Flow

#### Initial Generation Flow
1. User opens chat interface in WordPress editor
2. User describes what they want to build
3. System shows AI outline of structure
4. System generates pattern (loading indicator shown)
5. Preview updates in editor
6. User sees completion message with action buttons
7. User can insert pattern or continue refining

#### Refinement Flow
1. User has generated pattern visible in preview
2. User describes desired changes
3. System applies modifications (loading indicator)
4. Preview updates with changes
5. User can continue refining or insert pattern

## Code Implementation Guidelines

### WordPress Plugin Structure
```
project-particles/
├── project-particles.php (main plugin file)
├── includes/
│   ├── class-particles-chat.php (chat functionality)
│   ├── class-particles-ai.php (AI integration)
│   ├── class-particles-theme.php (theme.json parsing)
│   └── class-particles-patterns.php (pattern handling)
├── assets/
│   ├── js/
│   │   └── chat-interface.js (React components)
│   └── css/
│       └── chat-interface.css
└── build/ (compiled assets)
```

### React Component Structure
```jsx
<ChatInterface>
  <ChatHeader />
  <MessageList>
    <SystemMessage />
    <UserMessage />
    <AIMessage />
  </MessageList>
  <ChatInput />
</ChatInterface>
```

### State Management
**Required State:**
- `messages` - Array of all conversation messages
- `currentPattern` - Latest generated pattern markup
- `isLoading` - AI processing status
- `themeConfig` - Parsed theme.json data
- `conversationHistory` - For refinements (initial + last 5-10)

### WordPress Integration Points

#### Gutenberg/Block Editor
- Register custom sidebar panel
- Use `@wordpress/plugins` API
- Hook into editor store for pattern insertion

#### Site Editor
- Register as Site Editor sidebar
- Use `@wordpress/edit-site` hooks
- Integration with template/pattern management

#### REST API Endpoints
```
POST /wp-json/particles/v1/plan
POST /wp-json/particles/v1/generate
POST /wp-json/particles/v1/refine
GET  /wp-json/particles/v1/theme-config
```

## AI System Prompts (Templates)

### Planning Phase Prompt
```
You are a WordPress design assistant helping users create Block Patterns.

TASK: Analyze the user's request and create a structured outline.

USER REQUEST: {user_input}

REQUIREMENTS:
- Identify the type of content (landing page, about page, etc.)
- Break down into logical sections
- Suggest appropriate WordPress core blocks for each section
- Return structured JSON outline

CONSTRAINTS:
- Use only core WordPress blocks
- Consider typical web design best practices
- Keep sections focused and purposeful

Return only the JSON outline, no additional text.
```

### Generation Phase Prompt
```
You are a WordPress Block Pattern generator.

TASK: Generate complete WordPress block markup based on the outline.

OUTLINE: {outline_json}
THEME CONFIGURATION: {theme_json}

REQUIREMENTS:
- Generate valid WordPress block markup (HTML comments format)
- Use ONLY core WordPress blocks
- Apply styling from theme.json (colors, typography, spacing)
- Write actual content based on subject matter (placeholder content acceptable)
- Add "context" attribute to all top-level blocks with medium-detail explanation

CONTEXT ATTRIBUTE FORMAT:
- Free-form text describing purpose + key design decisions + content intent
- Example: "Hero section introducing [subject] with [design choice], emphasizing [key message]"
- Avoid sensitive information

CONSTRAINTS:
- All colors must be from theme.json palette
- All font sizes must use theme.json scale
- All spacing must use theme.json spacing scale
- Ensure proper block nesting and valid markup

Return only the block markup, no additional explanation.
```

### Refinement Phase Prompt
```
You are modifying an existing WordPress Block Pattern.

TASK: Apply the requested changes to the pattern.

USER REQUEST: {refinement_request}
CURRENT PATTERN: {current_pattern_markup}
CONVERSATION HISTORY: {history}
THEME CONFIGURATION: {theme_json}

REQUIREMENTS:
- Apply all requested modifications
- Return complete updated pattern (not just changed sections)
- Update context attributes for significantly modified sections
- Maintain consistency with theme.json constraints

HANDLING EDGE CASES:
- If request is ambiguous, use conversation history to infer intent
- If request is impossible with core blocks, do best approximation and explain
- If theme doesn't support requested styling, suggest closest alternative

Return the complete updated pattern markup.
```

## Development Priorities

### Phase 1: Core Chat Interface ✓
- Chat UI component (multiple design variants available)
- Message display and state management
- Input handling and basic interactions

### Phase 2: WordPress Integration (Current)
- Plugin setup and structure
- Editor sidebar registration
- Preview rendering in editor

### Phase 3: AI Integration
- API endpoint setup
- OpenAI/Claude API integration
- Request/response handling
- Error handling and retries

### Phase 4: Pattern Management
- Pattern insertion into editor
- Preview system implementation
- Pattern validation
- Conversation persistence

### Phase 5: Polish & Testing
- UI refinements
- Performance optimization
- Error handling
- User testing and feedback

## Best Practices

### Code Quality
- Follow WordPress Coding Standards
- Use TypeScript for React components (if available)
- Comprehensive error handling
- Input validation and sanitization
- Security: nonce verification, capability checks

### Performance
- Lazy load chat interface
- Debounce API requests
- Cache theme.json data
- Optimize bundle size
- Progressive enhancement

### User Experience
- Clear loading states
- Helpful error messages
- Keyboard shortcuts (Enter to send, Shift+Enter for new line)
- Responsive design
- Accessible components (ARIA labels, keyboard navigation)

### Documentation
- Inline code comments for complex logic
- JSDoc for functions
- README for setup and usage
- API documentation for endpoints

## Testing Considerations

### Unit Tests
- Theme.json parsing logic
- Pattern validation
- Message state management
- API response handling

### Integration Tests
- WordPress editor integration
- REST API endpoints
- Pattern insertion flow
- Refinement workflow

### E2E Tests
- Complete user flows
- Multi-step conversations
- Error scenarios
- Different theme configurations

## Common Issues & Solutions

### Issue: Pattern doesn't respect theme colors
**Solution**: Ensure theme.json parsing correctly extracts palette and applies slugs

### Issue: AI generates invalid block markup
**Solution**: Validate block structure before preview, provide clear error feedback

### Issue: Context attributes too long
**Solution**: AI prompt should emphasize conciseness, validate length on backend

### Issue: Preview doesn't update
**Solution**: Check editor store updates, ensure pattern format is correct

### Issue: Conversation history grows too large
**Solution**: Implement trimming logic (keep initial + last 5-10 messages)

## Security Checklist

- [ ] Validate and sanitize all user input
- [ ] Use nonces for AJAX requests
- [ ] Check user capabilities before operations
- [ ] Escape output in WordPress functions
- [ ] Validate AI-generated markup before insertion
- [ ] Rate limit API requests
- [ ] Secure API key storage
- [ ] Prevent XSS in chat messages
- [ ] CSRF protection on forms

## Helpful WordPress Functions

### Block Pattern Registration
```php
register_block_pattern()
register_block_pattern_category()
```

### Editor Integration
```php
add_action('enqueue_block_editor_assets', 'callback');
wp_enqueue_script()
wp_localize_script() // Pass data to JS
```

### REST API
```php
register_rest_route()
rest_ensure_response()
new WP_Error()
```

### Theme.json Access
```php
wp_get_global_settings()
wp_get_global_styles()
```

## Resources

- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [theme.json Documentation](https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-json/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [@wordpress/scripts Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)

---

## Instructions for AI Assistant

When helping with this project:

1. **Always consider WordPress constraints** - Only suggest core blocks, respect theme.json
2. **Follow established patterns** - Use the AI flow architecture documented above
3. **Prioritize user experience** - Clear feedback, helpful errors, smooth interactions
4. **Write production-ready code** - Include error handling, validation, documentation
5. **Think about WordPress integration** - Consider how features fit into WordPress ecosystem
6. **Reference existing documentation** - Use PROJECT_CONTEXT.md and Backend AI Flow documents
7. **Ask clarifying questions** - If requirements are unclear, ask before implementing
8. **Provide context** - Explain why certain approaches are recommended
9. **Consider security** - WordPress security best practices are critical
10. **Test-driven mindset** - Think about how features can be tested

**Current Phase**: Frontend Implementation - WordPress Plugin Integration
**Next Steps**: Build WordPress plugin structure and integrate chat UI into editor

---

**Last Updated**: Phase 2 Documentation Complete
**For**: AI Code Assistants (Cursor, VSCode, Claude Code, etc.)