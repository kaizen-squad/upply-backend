#!/bin/bash

set -e  # Arrêter en cas d'erreur

echo "🧹 Nettoyage de toutes les branches feature..."
echo ""

# Mettre à jour develop
echo "📥 Mise à jour de develop..."
git checkout develop
git pull origin develop
echo "✅ develop mis à jour"
echo ""

# Tableau des branches dans l'ordre de création (du plus ancien au plus récent)
# Ajustez cet ordre selon vos besoins
BRANCHES=(
    "feature/architecture"
    "feature/migrations"
    "feature/dockerization"
    "feature/seeders"
    "feature/taskService"
    "feature/applicationService"
    "feature/deliverableService"
    "feature/FedaPayService"
)

# Fonction pour rebaser une branche
rebase_branch() {
    local branch=$1
    
    echo "🔄 Traitement de $branch..."
    
    # Vérifier si la branche existe
    if ! git show-ref --verify --quiet refs/heads/$branch; then
        echo "⚠️  La branche $branch n'existe pas localement, passage à la suivante"
        echo ""
        return
    fi
    
    # Checkout de la branche
    git checkout $branch
    
    # Sauvegarder avant le rebase
    git branch backup-$branch-$(date +%Y%m%d-%H%M%S) || true
    
    # Rebaser sur develop
    echo "  Rebase sur develop..."
    if git rebase develop; then
        echo "  ✅ Rebase réussi sans conflits"
        
        # Push avec force-with-lease
        echo "  🚀 Push vers origin..."
        if git push --force-with-lease origin $branch; then
            echo "  ✅ Push réussi"
        else
            echo "  ⚠️  Erreur lors du push (normal si la branche n'existe pas sur origin)"
        fi
    else
        echo ""
        echo "  ⚠️  CONFLITS DÉTECTÉS sur $branch !"
        echo "  📝 Actions à faire :"
        echo "     1. Résolvez les conflits dans les fichiers"
        echo "     2. git add ."
        echo "     3. git rebase --continue"
        echo "     4. Relancez ce script pour continuer"
        echo ""
        echo "  Ou pour annuler le rebase de cette branche :"
        echo "     git rebase --abort"
        echo ""
        exit 1
    fi
    
    echo ""
}

# Traiter chaque branche
for branch in "${BRANCHES[@]}"; do
    rebase_branch "$branch"
done

echo "🎉 Toutes les branches ont été rebasées sur develop !"
echo ""
echo "📋 Résumé :"
git branch -vv

echo ""
echo "✅ Terminé ! Toutes vos branches sont maintenant basées sur develop."
