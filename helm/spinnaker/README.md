# Install the spin CLI

https://www.spinnaker.io/setup/spin/

Note: spin CLI >= 1.18.x is required for Spinnaker >= 1.22.x

# Get the spin CLI credential file

```
gsutil cp gs://vimeet-values-staging/spin-staging.yaml spin-staging.yaml
gsutil cp gs://vimeet-values-prod/spin-prod.yaml spin-prod.yaml
```

# Save updated pipelines and pipeline templates

Linux / macOS bash :

```
find ./templates -type f -name "*.json" -exec spin --config spin-staging.yaml pipeline-template save -f {} \;
find ./pipelines -type f -name "*.json" -exec spin --config spin-staging.yaml pipeline save -f {} \;
```

Windows PowerShell :

```
Get-ChildItem ".\templates" -Recurse -Filter *.json | % { & spin --config spin-staging.yaml pipeline-template save -f $_.FullName }
Get-ChildItem ".\pipelines" -Recurse -Filter *.json | % { & spin --config spin-staging.yaml pipeline save -f $_.FullName }
```
