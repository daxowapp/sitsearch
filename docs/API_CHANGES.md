# API Changes
- [2026-04-16] SIT Program Recommender: Deprecated over 20 legacy REST API endpoint routes (e.g. `/quiz/start`, `/quiz/submit`) and consolidated all AI inference loops entirely into `/chat/question` and `/chat/recommend` enforcing strict JSON Object mode schemas.
