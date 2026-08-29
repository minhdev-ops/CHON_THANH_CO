const ESTABLISHED_YEAR = 2005

export function getYearsOfExperience(): number {
  return new Date().getFullYear() - ESTABLISHED_YEAR
}

export function getExperienceText(): string {
  const years = getYearsOfExperience()
  return `${years}+ năm kinh nghiệm`
}
