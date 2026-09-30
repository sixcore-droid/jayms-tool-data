#!/usr/bin/env bash
# Versioned backups for the jayms.com tool snippets.
#
#   ./snap.sh snap  <file> <snippet-id> "what changed"   take v+1 and deploy it
#   ./snap.sh list  <file>                                show the versions
#   ./snap.sh show  <file> <vNNN>                         print one version's note
#   ./snap.sh restore <file> <snippet-id> <vNNN>          put that version back live
#
# A restore is itself a new version, so going back is never destructive and
# the log keeps reading forwards.
set -euo pipefail

REPO=sixcore-droid/jayms-tool-data
HERE="$(cd "$(dirname "$0")" && pwd)"
CMD="${1:-}"; shift || true

base()  { basename "$1" .php; }
vdir()  { echo "$HERE/versions"; }
vlog()  { echo "$(vdir)/VERSIONS.md"; }
next()  { ls "$(vdir)" | grep -o "$(base "$1")\.v[0-9]\{3\}\.php" | sed "s/.*\.v\([0-9]*\)\.php/\1/" |
          sort -n | tail -1 | awk '{printf "v%03d", $1+1}'; }

case "$CMD" in
  snap)
    FILE="$1"; ID="$2"; MSG="$3"
    V="$(next "$FILE")"; [ -n "$V" ] || V=v001
    cp "$FILE" "$(vdir)/$(base "$FILE").$V.php"
    SHA="$(gh api "repos/$REPO/contents/snippet/interleaved-bible-rebuild.php" -q .sha)"
    python3 - "$FILE" "$SHA" "$MSG" <<'PY'
import base64, json, sys
json.dump({"message": sys.argv[3], "content": base64.b64encode(open(sys.argv[1],'rb').read()).decode(),
           "sha": sys.argv[2], "branch": "main"}, open('/tmp/snap.json','w'))
PY
    C="$(gh api -X PUT "repos/$REPO/contents/snippet/interleaved-bible-rebuild.php" --input /tmp/snap.json -q .commit.sha)"
    printf '| %s | %s | `%s` | %s |\n' "$V" "$(date +%F)" "${C:0:8}" "$MSG" >> "$(vlog)"
    echo "$V $C"
    ;;
  list)    grep -E '^\| v[0-9]{3} \| 20' "$(vlog)" ;;
  show)    grep -E "^\| ${2:-${1:-}} " "$(vlog)" ;;
  restore)
    FILE="$1"; ID="$2"; V="$3"
    SRC="$(vdir)/$(base "$FILE").$V.php"
    [ -f "$SRC" ] || { echo "no such version: $V" >&2; exit 1; }
    cp "$SRC" "$FILE"
    "$0" snap "$FILE" "$ID" "restore $V"
    ;;
  *) sed -n '2,12p' "$0"; exit 1 ;;
esac
